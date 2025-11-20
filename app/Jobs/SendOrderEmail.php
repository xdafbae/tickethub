<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendOrderEmail implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $orderId) {}

    public function handle(): void
    {
        $order = Order::find($this->orderId);
        if (!$order || !$order->buyer_email) return;

        $subject = match($order->status) {
            'paid' => 'Pembayaran Berhasil - Order #' . $order->id,
            'expired' => 'Pembayaran Kedaluwarsa - Order #' . $order->id,
            'failed' => 'Pembayaran Gagal - Order #' . $order->id,
            default => 'Status Order Diperbarui - Order #' . $order->id,
        };

        $body = "Halo {$order->buyer_name},\n\n"
            . "Status order Anda: {$order->status}\n"
            . "Event: {$order->event->title}\n"
            . "Total: Rp " . number_format($order->total, 0, ',', '.') . "\n\n"
            . "Terima kasih.";

        Mail::raw($body, function ($m) use ($order, $subject) {
            $m->to($order->buyer_email)->subject($subject);
        });
    }
}