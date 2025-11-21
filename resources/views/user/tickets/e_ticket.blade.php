<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>E‑Ticket - Order #{{ $order->id }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; color:#111; }
        .wrap { padding: 20px; }
        .header { text-align:center; margin-bottom: 12px; }
        .title { font-size: 20px; font-weight: 700; }
        .sub { font-size: 12px; color:#555; }
        .card { border:1px solid #ddd; border-radius:8px; padding:16px; margin-top: 12px; }
        .grid { display: table; width: 100%; }
        .row { display: table-row; }
        .cell { display: table-cell; padding:6px 4px; font-size: 13px; }
        .label { color:#666; width: 32%; }
        .value { font-weight: 600; }
        .qr { text-align:center; margin-top: 16px; }
        /* Paksa ukuran SVG agar DomPDF merender dengan benar */
        .qr svg { width: 220px; height: 220px; display: inline-block; }
        .footer { font-size: 11px; color:#555; margin-top: 16px; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="header">
        <div class="title">E‑Ticket</div>
        <div class="sub">Order #{{ $order->id }} • Diterbitkan {{ $issuedAt->format('d M Y H:i') }}</div>
    </div>

    <div class="card">
        <div class="grid">
            <div class="row">
                <div class="cell label">Event</div>
                <div class="cell value">{{ $event->title }}</div>
            </div>
            <div class="row">
                <div class="cell label">Nama Pemesan</div>
                <div class="cell value">{{ $buyerName }}</div>
            </div>
            <div class="row">
                <div class="cell label">Order ID (App)</div>
                <div class="cell value">#{{ $order->id }}</div>
            </div>
            <div class="row">
                <div class="cell label">Order ID (Midtrans)</div>
                <div class="cell value">{{ $order->external_ref ?? '-' }}</div>
            </div>
            <div class="row">
                <div class="cell label">Total</div>
                <div class="cell value">Rp {{ number_format($order->total, 0, ',', '.') }}</div>
            </div>
        </div>

        <div class="qr">
            @if(!empty($qrPngPath))
                <img src="{{ $qrPngPath }}" alt="QR Code" style="width:220px;height:220px;">
            @elseif(!empty($qrDataUri))
                <img src="{{ $qrDataUri }}" alt="QR Code" style="width:220px;height:220px;">
            @elseif(!empty($qrSvg))
                <div style="display:inline-block;width:220px;height:220px;">{!! $qrSvg !!}</div>
            @else
                <div style="width:220px;height:220px;border:1px dashed #ccc;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;color:#666;font-size:12px;">
                    QR tidak tersedia
                </div>
            @endif
            <div class="sub">Signature: {{ $signature ?? '-' }}</div>
        </div>
    </div>

    <div class="footer">
        Tunjukkan QR ini saat masuk venue. QR memuat data terverifikasi (HMAC) untuk validasi keaslian tiket.
    </div>
</div>
</body>
</html>