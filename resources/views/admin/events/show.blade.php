@extends('layouts.app')

@section('title', 'Detail Event')
@section('page-title', 'Detail Event')

@section('header-actions')
<a href="{{ url()->previous() }}" class="btn btn-secondary">← Kembali</a>
@endsection

@section('content')
<div class="table-card">
    <div class="table-header" style="justify-content: space-between;">
        <h3>{{ $event->title }}</h3>
        <div style="display:flex; gap:8px;">
            <a href="{{ route('admin.seat_map.builder', $event) }}" class="btn btn-primary btn-sm">Seat Map</a>
            <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-secondary btn-sm">Edit</a>
        </div>
    </div>
    <div style="padding: 24px; display: grid; grid-template-columns: 1fr 2fr; gap: 24px;">
        <div>
            @if($event->poster)
                <img src="{{ asset('storage/'.$event->poster) }}" alt="Poster" style="width:100%; max-width:300px; border-radius:12px;" />
            @endif
        </div>
        <div>
            <p><strong>Kategori:</strong> {{ $event->category }}</p>
            <p><strong>Tanggal:</strong> {{ $event->date->format('d M Y') }}</p>
            <p><strong>Lokasi:</strong> {{ $event->location }}</p>
            <p><strong>Kuota:</strong> {{ $event->quota }}</p>
            <p><strong>Deskripsi:</strong></p>
            <p>{{ $event->description }}</p>
        </div>
    </div>
</div>

<!-- Seat Map preview -->
<div class="table-card" style="margin-top: 20px;">
    <div class="table-header" style="justify-content: space-between;">
        <h3>Seat Map</h3>
        <a href="{{ route('admin.seat_map.builder', $event) }}" class="btn btn-secondary btn-sm">Edit Seat Map</a>
    </div>

    @if(isset($seatMap) && is_array($seatMap->layout) && count($seatMap->layout))
        <div id="seatMapCanvas" style="padding:20px; position:relative; height:720px; background:#fff; border:1px solid var(--admin-border); border-radius:10px; background-image: linear-gradient(#f3f4f6 1px, transparent 1px), linear-gradient(90deg, #f3f4f6 1px, transparent 1px); background-size:20px 20px;"></div>
        <div style="padding:12px; display:flex; gap:8px; flex-wrap:wrap;">
            @foreach(($event->ticketTypes ?? []) as $t)
                @php
                    $clr = match($t->name){
                        'VIP' => 'chip-purple',
                        'Reguler' => 'chip-blue',
                        'Gold' => 'chip-yellow',
                        default => 'chip-gray'
                    };
                @endphp
                <span class="chip {{ $clr }}">{{ $t->name }}</span>
            @endforeach
            <span class="chip chip-yellow">Dipilih</span>
            <span class="chip chip-red">Tidak Tersedia</span>
        </div>
    @else
        <div style="padding: 20px; color: var(--admin-muted);">
            Belum ada seat map untuk event ini.
        </div>
    @endif
</div>

<div class="table-card" style="margin-top: 20px;">
    <div class="table-header">
        <h3>Tipe Tiket</h3>
    </div>
    <table>
        <thead>
            <tr>
                <th>Nama</th>
                <th>Harga</th>
                <th>Kuota</th>
                <th>Aktif</th>
            </tr>
        </thead>
        <tbody>
            @forelse($event->ticketTypes as $type)
            <tr>
                <td>{{ $type->name }}</td>
                <td>Rp {{ number_format($type->price,0,',','.') }}</td>
                <td>{{ $type->quota }}</td>
                <td>{{ $type->is_active ? 'Ya' : 'Tidak' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align:center; color:var(--admin-muted); padding: 24px;">Belum ada tipe tiket untuk event ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

@section('additional-js')
<script>
(function(){
    var seatMap = {!! json_encode(($seatMap->layout ?? [])) !!};
    var canvas = document.getElementById('seatMapCanvas');
    if (!canvas || !Array.isArray(seatMap) || !seatMap.length) return;

    var types = {!! json_encode(($event->ticketTypes ?? collect())->map(fn($t)=>['id'=>$t->id,'name'=>$t->name])->values()) !!};
    function typeById(id){ return types.find(function(x){ return String(x.id)===String(id); }); }
    function colorFor(n){
        if (n.type==='stage' || n.type==='talent') return '#9ca3af';
        if (n.disabled) return '#fca5a5';
        var tt = n.ticket_type_id ? typeById(n.ticket_type_id) : null;
        if (tt){
            if (tt.name==='VIP') return '#ef4444';
            if (tt.name==='Gold') return '#f59e0b';
            if (tt.name==='Reguler') return '#3b82f6';
        }
        return '#e5e7eb';
    }

    seatMap.forEach(function(n){
        var el = document.createElement('div');
        el.style.position = 'absolute';
        el.style.width = (n.w||110) + 'px';
        el.style.height = (n.h||80) + 'px';
        el.style.border = '1px solid var(--admin-border)';
        el.style.borderRadius = (n.type==='chair' ? '50%' : '12px');
        el.style.display = 'flex';
        el.style.flexDirection = 'column';
        el.style.alignItems = 'center';
        el.style.justifyContent = 'center';
        el.style.boxShadow = '0 2px 4px rgba(0,0,0,0.06)';
        el.style.background = colorFor(n);
        el.style.userSelect = 'none';
        el.style.transform = 'translate('+(n.x||0)+'px,'+(n.y||0)+'px)';

        var label = document.createElement('div');
        label.style.fontWeight = '700';
        label.textContent = n.label || (n.type==='talent' ? 'TALENT' : '');
        el.appendChild(label);

        if ((n.type||'').indexOf('table')===0 && n.seats){
            var sc = document.createElement('div');
            sc.style.fontSize = '12px';
            sc.textContent = (n.seats||4) + ' kursi';
            el.appendChild(sc);
        }

        canvas.appendChild(el);
    });
})();
</script>
@endsection