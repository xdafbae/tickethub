<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;

class SendOrderEmail implements ShouldQueue
{
    use Queueable;

    public int $orderId;

    public function __construct(int $orderId)
    {
        $this->orderId = $orderId;
    }

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

        if ($order->status === 'paid') {
            // Generate signature & QR, render PDF, attach ke email
            $secret = (string)env('E_TICKET_SECRET', '');

            $payload = [
                'order_id'     => $order->id,
                'user_id'      => $order->user_id,
                'external_ref' => $order->external_ref,
                'issued_at'    => now()->toIso8601String(),
            ];
            $payloadJson = json_encode($payload, JSON_UNESCAPED_SLASHES);
            $signature   = $secret !== '' ? hash_hmac('sha256', (string)$payloadJson, $secret) : '';

            $qrData = json_encode(['v' => 1, 'd' => $payload, 'sig' => $signature], JSON_UNESCAPED_SLASHES);

            // Utama: hasilkan PNG agar render PDF lebih kompatibel
            $qrPng = Builder::create()
                ->writer(new PngWriter())
                ->data((string)$qrData)
                ->encoding(new Encoding('UTF-8'))
                ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
                ->size(220)
                ->margin(10)
                ->build();

            $qrPngBinary = $qrPng->getString();
            $qrDataUri = 'data:image/png;base64,' . base64_encode($qrPngBinary);

            // Simpan ke storage agar DomPDF bisa membaca dari path absolut
            \Illuminate\Support\Facades\Storage::put(
                'public/tickets/qr-order-' . $order->id . '.png',
                $qrPngBinary
            );
            $qrPngPath = storage_path('app/public/tickets/qr-order-' . $order->id . '.png');

            // Opsional: siapkan SVG untuk non-PDF
            $qrSvgObj = Builder::create()
                ->writer(new SvgWriter())
                ->data((string)$qrData)
                ->encoding(new Encoding('UTF-8'))
                ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
                ->size(220)
                ->margin(10)
                ->build();
            $qrSvg = $qrSvgObj->getString();

            $pdf = Pdf::loadView('user.tickets.e_ticket', [
                'order'     => $order,
                'event'     => $order->event,
                'buyerName' => $order->buyer_name,
                'qrPngPath' => $qrPngPath ?? null,
                'qrDataUri' => $qrDataUri,
                'signature' => $signature,
                'issuedAt'  => now(),
            ]);

            $pdfBinary = $pdf->output();

            // Simpan salinan PDF ke storage untuk verifikasi
            \Illuminate\Support\Facades\Storage::put(
                'public/tickets/e-ticket-order-' . $order->id . '.pdf',
                $pdfBinary
            );

            Mail::raw($body, function ($m) use ($order, $subject, $pdfBinary) {
                $m->to($order->buyer_email)->subject($subject);
                $m->attachData($pdfBinary, 'e-ticket-order-' . $order->id . '.pdf', ['mime' => 'application/pdf']);
            });
            return;
        }

        Mail::raw($body, function ($m) use ($order, $subject) {
            $m->to($order->buyer_email)->subject($subject);
        });
    }
}