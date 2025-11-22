@extends('layouts.app')

@section('title', 'Order #' . $order->id)
@section('page-title', 'Order #' . $order->id)
@section('page-subtitle', $order->event?->title)

@section('header-actions')
@role('admin')
<form action="{{ route('admin.orders.status', $order) }}" method="POST" style="display:flex; gap:8px; align-items:center;">
    @csrf
    <select name="status" class="input">
        <option value="pending" {{ $order->status==='pending'?'selected':'' }}>pending</option>
        <option value="paid" {{ $order->status==='paid'?'selected':'' }}>paid</option>
        <option value="cancelled" {{ $order->status==='cancelled'?'selected':'' }}>cancelled</option>
        <option value="refunded" {{ $order->status==='refunded'?'selected':'' }}>refunded</option>
    </select>
    <button class="btn btn-secondary" type="submit">Ubah Status</button>
</form>
@endrole
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

<!-- Ringkasan: Order, Buyer, Event -->
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

<!-- Items dan Seats berdampingan -->
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
        <div class="table-header"><h3>Seats</h3></div>
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

<!-- Payments dan Refund berdampingan -->
<div class="stats-grid" style="grid-template-columns: 1fr 1fr; margin-top:16px;">
    <div class="table-card">
        <div class="table-header"><h3>Payments</h3></div>
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

    @role('admin')
    <div class="table-card">
        <div class="table-header"><h3>Proses Refund</h3></div>
        <form id="refundForm" method="POST" action="{{ route('admin.orders.refund', $order) }}" style="display:grid; gap:14px;">
            @csrf
            <div class="stats-grid" style="grid-template-columns: 1fr 1fr;">
                <div>
                    <label class="muted">Jenis Refund</label>
                    <div style="display:flex; gap:12px; margin-top:6px;">
                        <label style="display:flex; align-items:center; gap:8px;">
                            <input type="radio" name="refund_type" value="full" checked>
                            <span>Full Refund</span>
                        </label>
                        <label style="display:flex; align-items:center; gap:8px; opacity:.55;">
                            <input type="radio" name="refund_type" value="partial" disabled>
                            <span>Partial (segera)</span>
                        </label>
                    </div>
                    <p class="muted" style="margin-top:6px;">Saat ini sistem memproses full refund untuk total pesanan.</p>
                </div>
    
                <div>
                    <label class="muted">Jumlah yang Dikembalikan</label>
                    <div class="input" style="margin-top:6px; font-weight:600;">
                        Rp {{ number_format($order->total, 0, ',', '.') }}
                    </div>
                    <p class="muted" style="margin-top:6px;">Nilai berdasarkan total pada order ini.</p>
                </div>
            </div>
    
            <div class="stats-grid" style="grid-template-columns: 1fr 1fr;">
                <div class="form-grid">
                    <div class="form-field">
                        <label class="form-label">Alasan</label>
                        <div class="select">
                            <select name="reason">
                                <option value="" selected>Pilih alasan (opsional)</option>
                                <option value="Event dibatalkan">Event dibatalkan</option>
                                <option value="Perubahan jadwal">Perubahan jadwal</option>
                                <option value="Double payment">Double payment</option>
                                <option value="Permintaan pelanggan">Permintaan pelanggan</option>
                            </select>
                        </div>
                        <p class="helper">Opsional; jika diisi akan disertakan dalam email ke pembeli.</p>
                    </div>
                    <div class="form-field">
                        <label class="form-label">Catatan Internal</label>
                        <div class="textarea">
                            <textarea name="note" placeholder="Catatan internal (hanya untuk admin)"></textarea>
                        </div>
                        <p class="helper">Tidak dikirim ke pembeli; hanya terlihat oleh admin.</p>
                    </div>
                </div>
            </div>
    
            <div style="background:#fff7ed; border:1px solid #fed7aa; color:#7c2d12; padding:12px 14px; border-radius:10px;">
                <strong>Konfirmasi:</strong> Refund akan menandai order sebagai <em>refunded</em>, menonaktifkan akses tiket, mencatat payment refund, dan mengirim email notifikasi ke pembeli.
            </div>
    
            <label style="display:flex; align-items:center; gap:10px;">
                <input type="checkbox" id="confirmRefund">
                <span>Saya memahami konsekuensi dan setuju memproses refund</span>
            </label>
    
            <div style="display:flex; gap:10px;">
                <button type="submit" id="refundSubmit" class="btn btn-danger" disabled>Proses Refund</button>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-outline">Kembali</a>
            </div>
        </form>
    </div>
    @endrole
</div>
@endsection

@section('additional-js')
<script>
(function(){
    var cb = document.getElementById('confirmRefund');
    var btn = document.getElementById('refundSubmit');
    if (cb && btn) {
        btn.disabled = !cb.checked;
        cb.addEventListener('change', function(){ btn.disabled = !cb.checked; });
    }
})();
</script>
@endsection