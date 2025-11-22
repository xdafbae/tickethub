@extends('layouts.app')

@section('title', 'Order #' . $order->id)
@section('page-title', 'Order #' . $order->id)
@section('page-subtitle', $order->event?->title)

@section('header-actions')
<div style="display:flex; gap:8px;">
    <a href="{{ route('user.dashboard') }}" class="btn btn-outline">Kembali</a>
    @if($order->status === 'paid')
        <a href="{{ route('user.orders.ticket.download', ['order' => $order->id]) }}?refresh=1" class="btn btn-success">Download Tiket</a>
    @else
        <a href="{{ route('user.payment.status', ['order' => $order->id]) }}" class="btn btn-secondary">Cek Status Pembayaran</a>
    @endif
</div>
@endsection

@section('content')
@php
    $statusLabelColor = match($order->status) {
        'paid' => '#16a34a',
        'pending' => '#eab308',
        'cancelled' => '#64748b',
        'refunded' => '#ef4444',
        default => '#64748b',
    };
@endphp

<div class="stats-grid" style="grid-template-columns: repeat(3, 1fr);">
    <div class="stat-card">
        <p class="stat-card-title">Ringkasan Order</p>
        <p class="stat-card-value">#{{ $order->id }}</p>
        <p class="muted">Total: Rp {{ number_format($order->total, 0, ',', '.') }}</p>
        <p class="muted">Subtotal: Rp {{ number_format($order->subtotal, 0, ',', '.') }} · Diskon: Rp {{ number_format($order->discount, 0, ',', '.') }}</p>
        <div style="margin-top:8px;">
            <span class="badge" style="background: {{ $statusLabelColor }}; color:#fff;">{{ $order->status }}</span>
            @if($order->checkin_status === 'used')
                <span class="badge" style="background:#1f2937; color:#fff; margin-left:6px;">Checked-in</span>
            @endif
        </div>
    </div>
    <div class="stat-card">
        <p class="stat-card-title">Pembeli</p>
        <p class="stat-card-value">{{ $order->buyer_name }}</p>
        <p class="muted">{{ $order->buyer_email }}</p>
        <p class="muted">{{ $order->buyer_phone }}</p>
    </div>
    <div class="stat-card">
        <p class="stat-card-title">Event</p>
        <p class="stat-card-value">{{ $order->event?->title ?? '-' }}</p>
        <p class="muted">Ref: {{ $order->external_ref ?? '-' }}</p>
        @if($order->checked_in_at)
            <p class="muted">Check-in: {{ $order->checked_in_at->format('d M Y H:i') }}</p>
        @endif
    </div>
</div>

<div class="stats-grid" style="grid-template-columns: 1fr 1fr; margin-top:16px;">
    <div class="table-card">
        <div class="table-header"><h3>Items</h3></div>
        <table>
            <thead><tr><th>Nama</th><th>Qty</th><th>Harga</th></tr></thead>
            <tbody>
            @forelse(($order->items ?? []) as $it)
                <tr>
                    <td>{{ $it['name'] ?? '-' }}</td>
                    <td>{{ $it['qty'] ?? 1 }}</td>
                    <td>Rp {{ number_format($it['price'] ?? 0, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="3" style="text-align:center; color:var(--admin-muted);">Tidak ada item</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-card">
        <div class="table-header"><h3>Seat Assignment</h3></div>
        <table>
            <thead><tr><th>Seat</th></tr></thead>
            <tbody>
            @forelse(($order->seats ?? []) as $seat)
                <tr><td>{{ is_array($seat) ? ($seat['label'] ?? json_encode($seat)) : $seat }}</td></tr>
            @empty
                <tr><td style="text-align:center; color:var(--admin-muted);">Tidak ada kursi</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="table-card" style="margin-top:16px;">
    <div class="table-header"><h3>Pembayaran</h3></div>
    <table>
        <thead><tr><th>Provider</th><th>Transaksi</th><th>Status</th><th>Jumlah</th></tr></thead>
        <tbody>
        @forelse($order->payments as $p)
            <tr>
                <td>{{ $p->provider }}</td>
                <td>{{ $p->provider_transaction_id }}</td>
                <td>{{ $p->status }}</td>
                <td>Rp {{ number_format($p->amount, 0, ',', '.') }}</td>
            </tr>
        @empty
            <tr><td colspan="4" style="text-align:center; color:var(--admin-muted);">Tidak ada pembayaran</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection