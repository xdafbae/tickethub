<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class ProcessMidtransWebhook implements ShouldQueue
{
    use Queueable;

    public array $payload;

    public function __construct(array $payload) { $this->payload = $payload; }

    public function handle(): void
    {
        $p = $this->payload;

        // Verifikasi signature
        $serverKey = config('services.midtrans.server_key', env('MIDTRANS_SERVER_KEY'));
        $expected = hash('sha512', ($p['order_id'] ?? '') . ($p['status_code'] ?? '') . ($p['gross_amount'] ?? '') . $serverKey);
        if (($p['signature_key'] ?? '') !== $expected) {
            Log::warning('Midtrans signature mismatch', ['payload' => $p]);
            return;
        }

        $order = Order::where('external_ref', $p['order_id'] ?? null)->first();
        if (!$order) {
            Log::warning('Order not found for webhook', ['order_id' => $p['order_id'] ?? null]);
            return;
        }

        // Validasi nominal & currency
        $gross = (int)($p['gross_amount'] ?? 0);
        if ($gross !== (int)$order->total || ($p['currency'] ?? 'IDR') !== 'IDR') {
            Log::warning('Midtrans amount/currency mismatch', [
                'gross' => $gross,
                'expected' => (int)$order->total,
                'currency' => $p['currency'] ?? null,
                'order_id' => $order->id,
            ]);
            return;
        }

        $payment = Payment::where('order_id', $order->id)->latest()->first();

        // Map status Midtrans
        $trxStatus = $p['transaction_status'] ?? 'pending';
        $fraudStatus = $p['fraud_status'] ?? null;

        $targetPaymentStatus = 'pending';
        $targetOrderStatus = 'pending';

        if (in_array($trxStatus, ['capture','settlement'], true) && ($fraudStatus === null || $fraudStatus === 'accept')) {
            $targetPaymentStatus = 'paid';
            $targetOrderStatus = 'paid';
        } elseif (in_array($trxStatus, ['cancel','deny'], true)) {
            $targetPaymentStatus = 'failed';
            $targetOrderStatus = 'failed';
        } elseif ($trxStatus === 'expire') {
            $targetPaymentStatus = 'expired';
            $targetOrderStatus = 'expired';
        } else {
            $targetPaymentStatus = 'pending';
            $targetOrderStatus = 'pending';
        }

        // Update payment & order
        if ($payment) {
            $payment->update([
                'provider_transaction_id' => $p['transaction_id'] ?? $payment->provider_transaction_id,
                'status' => $targetPaymentStatus,
                'raw_payload' => $p,
            ]);
        }
        $order->update(['status' => $targetOrderStatus]);

        // Kirim email via job terpisah
        dispatch(new SendOrderEmail($order->id));
    }
}