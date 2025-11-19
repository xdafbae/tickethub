@php
    $formatId = fn($n) => number_format($n, 0, ',', '.');
@endphp
<section class="container" style="max-width:1024px;margin:24px auto;padding:16px;">
    <h2 style="margin-bottom:12px;">Cart untuk: {{ $event->title }}</h2>

    @if(session('status'))
        <div style="background:#e8f5e9;border:1px solid #a5d6a7;padding:8px 12px;border-radius:8px;margin-bottom:12px;">
            {{ session('status') }}
        </div>
    @endif
    @if($errors->any())
        <div style="background:#fdecea;border:1px solid #f5c6cb;padding:8px 12px;border-radius:8px;margin-bottom:12px;">
            {{ implode(' ', $errors->all()) }}
        </div>
    @endif

    <form method="POST" action="{{ route('user.cart.update', ['event' => $event->id]) }}" style="display:flex;flex-direction:column;gap:16px;">
        @csrf

        <div style="border:1px solid #444;border-radius:12px;padding:16px;background:#121212;color:#fff;">
            <h3 style="margin:0 0 12px 0;">Data Pembeli</h3>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:12px;">
                <div>
                    <label>Nama</label>
                    <input type="text" name="buyer[name]" value="{{ old('buyer.name', $cart['buyer']['name'] ?? '') }}" style="width:100%;padding:8px;border-radius:8px;border:1px solid #555;background:#1e1e1e;color:#fff;" required>
                </div>
                <div>
                    <label>Email</label>
                    <input type="email" name="buyer[email]" value="{{ old('buyer.email', $cart['buyer']['email'] ?? '') }}" style="width:100%;padding:8px;border-radius:8px;border:1px solid #555;background:#1e1e1e;color:#fff;" required>
                </div>
                <div>
                    <label>Telepon</label>
                    <input type="text" name="buyer[phone]" value="{{ old('buyer.phone', $cart['buyer']['phone'] ?? '') }}" style="width:100%;padding:8px;border-radius:8px;border:1px solid #555;background:#1e1e1e;color:#fff;">
                </div>
            </div>
        </div>

        <div style="border:1px solid #444;border-radius:12px;padding:16px;background:#121212;color:#fff;">
            <h3 style="margin:0 0 12px 0;">Pilih Tipe Tiket & Qty</h3>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:12px;">
                @foreach($ticketTypes as $tt)
                    <div style="border:1px solid #555;border-radius:10px;padding:12px;display:flex;gap:12px;align-items:center;background:#171717;">
                        <div style="flex:1;">
                            <div style="font-weight:600;">{{ $tt->name }}</div>
                            <div style="opacity:0.8;">Rp {{ $formatId($tt->price) }}</div>
                            <div style="opacity:0.7;font-size:12px;">Stok: {{ $tt->quota ?? '—' }}</div>
                        </div>
                        <div>
                            @php $val = old('items.'.$tt->id, $cart['items'][$tt->id] ?? 0); @endphp
                            <input type="number" min="0" name="items[{{ $tt->id }}]" value="{{ $val }}" style="width:96px;padding:8px;border-radius:8px;border:1px solid #555;background:#1e1e1e;color:#fff;">
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div style="border:1px solid #444;border-radius:12px;padding:16px;background:#121212;color:#fff;">
            <h3 style="margin:0 0 12px 0;">Seat Terpilih (terkunci oleh Anda)</h3>
            @php $seats = $cart['seats'] ?? []; @endphp
            @if(empty($seats))
                <div style="opacity:0.7;">Belum ada seat. Silakan pilih di halaman detail event.</div>
            @else
                <div style="display:flex;flex-wrap:wrap;gap:8px;">
                    @foreach($seats as $sid)
                        @php $name = $seatNamesById[$sid] ?? $sid; $locked = $seatLocks[$sid]['by_me'] ?? false; @endphp
                        <label style="border:1px solid {{ $locked ? '#6a5acd' : '#999' }};padding:6px 10px;border-radius:16px;background:#1a1a1a;">
                            <input type="checkbox" name="seats[]" value="{{ $sid }}" checked style="margin-right:6px;"> {{ $name }}
                            @if(!$locked)
                                <span style="color:#ff7675;margin-left:6px;">(tidak terkunci)</span>
                            @endif
                        </label>
                    @endforeach
                </div>
            @endif
        </div>

        <div style="border:1px solid #444;border-radius:12px;padding:16px;background:#121212;color:#fff;display:flex;justify-content:space-between;align-items:center;">
            <div>Total: <strong>Rp {{ $formatId($total) }}</strong></div>
            <div style="display:flex;gap:8px;">
                <a href="{{ route('user.events.show', ['event' => $event->id]) }}" style="padding:10px 14px;border-radius:8px;border:1px solid #555;background:#222;color:#fff;text-decoration:none;">Kembali ke Event</a>
                <button type="submit" style="padding:10px 14px;border-radius:8px;border:0;background:#6a5acd;color:#fff;">Lanjut Checkout</button>
            </div>
        </div>

    </form>
</section>