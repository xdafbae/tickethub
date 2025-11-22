<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pengembalian Dana Order #{{ $order->id }}</title>
</head>
<body style="font-family: system-ui, -apple-system, Segoe UI, Roboto; color:#111;">
    <h2>Pengembalian Dana Order #{{ $order->id }}</h2>
    <p>Halo {{ $order->buyer_name }},</p>
    <p>Kami telah memproses pengembalian dana untuk pesanan Anda pada event <strong>{{ $order->event?->title }}</strong>.</p>
    <ul>
        <li>Total: Rp {{ number_format($order->total, 0, ',', '.') }}</li>
        @if(!empty($reason))
        <li>Alasan: {{ $reason }}</li>
        @endif
    </ul>
    @if(!empty($note))
    <p><em>Catatan:</em> {{ $note }}</p>
    @endif
    <p>Status pesanan kini: <strong>{{ $order->status }}</strong>.</p>
    <p>Terima kasih telah menggunakan TicketHub.</p>
</body>
</html>