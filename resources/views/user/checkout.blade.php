@extends('layouts.user')

@section('title', 'Checkout - ' . $event->title)
@section('page-title', 'Checkout')

@section('content')
@php
    $formatId = fn($n) => number_format((int)$n, 0, ',', '.');
    $seats = $realTimeSeats ?? [];
    $discount = isset($discount) ? (int)$discount : 0;
    $finalTotal = isset($finalTotal) ? (int)$finalTotal : ($total ?? 0);
@endphp

<div class="container" style="max-width: 1100px; margin: 0 auto; padding: 16px;">
    <form method="POST" action="{{ route('user.checkout.confirm', ['event' => $event->id]) }}" style="margin:0;">
        @csrf

        <div style="border:1px solid var(--admin-border); border-radius:16px; background: rgba(255,255,255,0.06); overflow:hidden;">
            <div style="padding:20px;">
                <h2 style="margin:0 0 12px; font-size: 24px; font-weight: 800;">Checkout: {{ $event->title }}</h2>

                @if(session('status'))
                    <div style="background:#e8f5e9;border:1px solid #a5d6a7;padding:10px 12px;border-radius:8px;margin-bottom:12px;">
                        {{ session('status') }}
                    </div>
                @endif 
                @if($errors->any())
                    <div style="background:#fdecea;border:1px solid #f5c6cb;padding:10px 12px;border-radius:8px;margin-bottom:12px;">
                        {{ implode(' ', $errors->all()) }}
                    </div>
                @endif

                <!-- Grid utama: kiri Data Pembeli, kanan Ringkasan Order -->
                <div style="display:grid; grid-template-columns: 1.5fr 1fr; gap: 20px;">
                    <div class="table-card" style="margin:0;">
                        <h3 style="margin: 0 0 8px; font-size: 16px;">Data Pembeli</h3>
                        <div style="display:grid; grid-template-columns: 1fr; gap: 10px;">
                            <div>
                                <label style="display:block; font-size:12px; color: var(--admin-muted);">Nama</label>
                                <input type="text" name="buyer[name]" value="{{ old('buyer.name', $cart['buyer']['name'] ?? '') }}" style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--admin-border); background:#0f0f10; color:#fff;" required>
                            </div>
                            <div>
                                <label style="display:block; font-size:12px; color: var(--admin-muted);">Email</label>
                                <input type="email" name="buyer[email]" value="{{ old('buyer.email', $cart['buyer']['email'] ?? '') }}" style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--admin-border); background:#0f0f10; color:#fff;" required>
                            </div>
                            <div>
                                <label style="display:block; font-size:12px; color: var(--admin-muted);">Telepon</label>
                                <input type="text" name="buyer[phone]" value="{{ old('buyer.phone', $cart['buyer']['phone'] ?? '') }}" style="width:100%; padding:10px; border-radius:8px; border:1px solid var(--admin-border); background:#0f0f10; color:#fff;">
                            </div>
                        </div>
                    </div>

                    <div class="table-card" style="margin:0;">
                        <div class="table-header"><h3>Ringkasan Order</h3></div>
                        <div style="padding: 20px;">
                            <table style="width:100%; border-collapse:collapse; background:transparent; color:#e5e7eb;">
                                <thead>
                                    <tr>
                                        <th style="text-align:left; color: var(--admin-muted); border-bottom:1px solid var(--admin-border);">Tiket</th>
                                        <th style="text-align:right; color: var(--admin-muted); border-bottom:1px solid var(--admin-border);">Qty</th>
                                        <th style="text-align:right; color: var(--admin-muted); border-bottom:1px solid var(--admin-border);">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($derivedItems as $item)
                                        <tr>
                                            <td style="background:transparent;">{{ $item['name'] }}</td>
                                            <td style="text-align:right; background:transparent;">{{ $item['qty'] }}</td>
                                            <td style="text-align:right; background:transparent;">Rp {{ $formatId($item['qty'] * $item['price']) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" style="text-align:center; color: var(--admin-muted); background:transparent;">Belum ada item.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="2" style="text-align:right; border-top:1px dashed var(--admin-border); background:transparent;"><strong>Total</strong></td>
                                        <td id="totalCell" style="text-align:right; border-top:1px dashed var(--admin-border); background:transparent;"><strong>Rp {{ $formatId($total ?? 0) }}</strong></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="text-align:right; background:transparent;">Diskon</td>
                                        <td id="discountCell" style="text-align:right; background:transparent;">Rp {{ $formatId($discount) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="text-align:right; background:transparent;"><strong>Total Bayar</strong></td>
                                        <td id="finalTotalCell" style="text-align:right; background:transparent;"><strong>Rp {{ $formatId($finalTotal) }}</strong></td>
                                    </tr>
                                </tfoot>
                            </table>

                            <!-- Input & tombol promo -->
                            <div style="margin-top:12px;">
                                <label for="promo_code" class="form-label">Kode Promo</label>
                                <div style="display:flex; gap:8px;">
                                    <input
                                        type="text"
                                        id="promo_code"
                                        name="promo_code"
                                        class="form-input"
                                        placeholder="Masukkan kode promo"
                                        value="{{ old('promo_code', session('checkout_promo_code')) }}"
                                        style="flex:1;"
                                    >
                                    <button type="button" id="applyPromoBtn" class="btn btn-secondary">Terapkan Promo</button>
                                </div>
                                <div id="promoMsg" style="color: var(--admin-muted); font-size:12px; margin-top:6px;">
                                    Diskon diverifikasi tanpa meninggalkan halaman.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Seat Dipilih + Buttons -->
                <div style="margin-top:16px; border:1px solid var(--admin-border); border-radius:10px; padding:12px; background: rgba(255,255,255,0.04); box-sizing:border-box;">
                    <h3 style="margin: 0 0 8px; font-size: 16px;">Seat Dipilih</h3>
                    @if(empty($seats))
                        <div style="color: var(--admin-muted);">Belum ada seat yang terkunci. Silakan kembali ke detail event untuk memilih kursi.</div>
                    @else
                        <div style="display:flex; gap:8px; flex-wrap:wrap;">
                            @foreach($seats as $sid)
                                @php
                                    $locked = $seatLocks[$sid]['by_me'] ?? false;
                                    $name = $seatNamesById[$sid] ?? $sid;
                                    $clr = $locked ? '#22c55e' : '#ef4444';
                                @endphp
                                <span class="chip" style="background: {{ $clr }}; color:#fff;">
                                    {{ $name }}{!! !$locked ? ' (unlocked)' : '' !!}
                                </span>
                            @endforeach
                        </div>
                        <div style="color: var(--admin-muted); margin-top:8px;">Pastikan kursi tetap terkunci sebelum konfirmasi.</div>
                    @endif
                </div>

                <!-- Tombol aksi -->
                <div style="margin-top:18px; display:flex; gap:8px;">
                    <a href="{{ route('user.events.show', ['event' => $event->id]) }}" class="btn btn-outline">Kembali ke detail</a>
                    <button type="submit" class="btn btn-primary">Konfirmasi Order</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
(function(){
    var applyBtn = document.getElementById('applyPromoBtn');
    var input = document.getElementById('promo_code');
    var msg = document.getElementById('promoMsg');
    var discountCell = document.getElementById('discountCell');
    var finalTotalCell = document.getElementById('finalTotalCell');
    var totalCell = document.getElementById('totalCell');
    var csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    function formatId(n){
        n = parseInt(n || 0, 10);
        return (n||0).toLocaleString('id-ID');
    }

    if (applyBtn) {
        applyBtn.addEventListener('click', function(){
            var code = (input.value || '').trim();
            if (!code) {
                msg.textContent = 'Masukkan kode promo terlebih dahulu.';
                msg.style.color = '#fca5a5';
                return;
            }
            applyBtn.disabled = true;
            applyBtn.textContent = 'Memverifikasi...';
            msg.textContent = 'Memverifikasi kode promo...';
            msg.style.color = 'var(--admin-muted)';

            fetch("{{ route('user.checkout.apply_promo', ['event' => $event->id]) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({ promo_code: code })
            })
            .then(function(r){ return r.json().then(function(d){ return { status: r.status, data: d }; }); })
            .then(function(res){
                var d = res.data || {};
                if (res.status >= 400 || d.ok === false) {
                    msg.textContent = d.error || 'Kode promo tidak valid.';
                    msg.style.color = '#fca5a5';
                    applyBtn.disabled = false;
                    applyBtn.textContent = 'Terapkan Promo';
                    return;
                }
                discountCell.textContent = 'Rp ' + formatId(d.discount);
                finalTotalCell.innerHTML = '<strong>Rp ' + formatId(d.final_total) + '</strong>';
                msg.textContent = d.message || ('Promo ' + code + ' diterapkan.');
                msg.style.color = '#22c55e';
                applyBtn.disabled = false;
                applyBtn.textContent = 'Terapkan Promo';
            })
            .catch(function(){
                msg.textContent = 'Terjadi kesalahan jaringan.';
                msg.style.color = '#fca5a5';
                applyBtn.disabled = false;
                applyBtn.textContent = 'Terapkan Promo';
            });
        });
    }
})();
</script>
@endsection