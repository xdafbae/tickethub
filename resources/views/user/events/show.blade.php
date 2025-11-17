@extends('layouts.user')

@section('title', $event->title)
@section('page-title', 'Detail Event')

@section('content')
<div class="container" style="max-width: 1100px; margin: 0 auto; padding: 16px;">
    <div style="border:1px solid var(--admin-border); border-radius:16px; background: rgba(255,255,255,0.06); overflow:hidden;">
        <div style="display:flex; gap:20px; align-items:flex-start; padding:20px; flex-wrap:wrap;">
            <div style="flex:0 0 360px; border-radius:12px; overflow:hidden; background:#0f0f10;">
                {{-- poster --}}
                @if($event->poster)
                    <img src="{{ asset('storage/'.$event->poster) }}" alt="{{ $event->title }}" style="width:100%; height:100%; max-height: 320px; object-fit:cover;">
                @else
                    <img src="https://picsum.photos/seed/{{ $event->id }}/800/480" alt="{{ $event->title }}" style="width:100%; height:100%; max-height: 320px; object-fit:cover;">
                @endif
            </div>

            <div style="flex:1 1 520px; min-width:280px;">
                <h2 style="margin:0 0 10px; font-size: 24px; font-weight: 800;">{{ $event->title }}</h2>
                <div style="display:flex; gap:10px; align-items:center; margin-bottom:12px; flex-wrap:wrap;">
                    <span class="chip chip-gray">{{ $event->category ?? 'Umum' }}</span>
                    <span style="color: var(--admin-muted);">📅 {{ $event->date?->locale('id')->translatedFormat('d M Y') }}</span>
                    <span style="color: var(--admin-muted);">📍 {{ $event->location ?? '—' }}</span>
                </div>

                @php
                    $types = $event->ticketTypes ?? collect();
                    $totalQuota = $types->sum('quota');
                @endphp
                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 12px; margin: 14px 0 18px; align-items:stretch;">
                    <div style="border:1px solid var(--admin-border); border-radius:10px; padding:12px; background: rgba(255,255,255,0.04); box-sizing:border-box;">
                        <div style="color: var(--admin-muted); font-size:12px;">Tanggal</div>
                        <div style="font-weight:700; word-break:break-word;">{{ $event->date?->locale('id')->translatedFormat('l, d M Y') }}</div>
                    </div>
                    <div style="border:1px solid var(--admin-border); border-radius:10px; padding:12px; background: rgba(255,255,255,0.04); box-sizing:border-box;">
                        <div style="color: var(--admin-muted); font-size:12px;">Lokasi</div>
                        <div style="font-weight:700; word-break:break-word;">{{ $event->location ?? '—' }}</div>
                    </div>
                    <div style="border:1px solid var(--admin-border); border-radius:10px; padding:12px; background: rgba(255,255,255,0.04); box-sizing:border-box;">
                        <div style="color: var(--admin-muted); font-size:12px;">Kuota</div>
                        <div style="font-weight:700;">{{ $event->quota ?? ($totalQuota ?: '—') }}</div>
                    </div>
                </div>

                {{-- Card Deskripsi --}}
                @if($event->description)
                    <div style="margin-top:12px; border:1px solid var(--admin-border); border-radius:10px; padding:12px; background: rgba(255,255,255,0.04); box-sizing:border-box;">
                        <h3 style="margin: 0 0 8px; font-size: 16px;">Deskripsi</h3>
                        <div style="color: var(--admin-muted); line-height:1.6; white-space: normal; overflow-wrap:anywhere; word-break:break-word;">
                            {!! nl2br(e($event->description)) !!}
                        </div>
                    </div>
                @endif

                {{-- Card Tipe Tiket --}}
                @if($types->count() > 0)
                    <div style="margin-top:16px; border:1px solid var(--admin-border); border-radius:10px; padding:12px; background: rgba(255,255,255,0.04); box-sizing:border-box;">
                        <h3 style="margin: 0 0 8px; font-size: 16px;">Tipe Tiket</h3>
                        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 10px; align-items:stretch;">
                            @foreach($types as $t)
                                @php
                                    $seatColor = match($t->name){
                                        'VIP' => '#ef4444',      // kursi VIP: merah
                                        'Gold' => '#f59e0b',     // kursi Gold: kuning
                                        'Reguler' => '#3b82f6',  // kursi Reguler: biru
                                        default => '#9ca3af',    // lainnya: abu
                                    };
                                @endphp
                                <div style="border:1px solid var(--admin-border); border-radius:10px; padding:10px; background: rgba(255,255,255,0.04); box-sizing:border-box; border-left:4px solid {{ $seatColor }};">
                                    <div style="display:flex; justify-content:space-between; align-items:center; gap:8px; flex-wrap:wrap;">
                                        <span class="chip" style="background: {{ $seatColor }}; color:#fff;">
                                            {{ $t->name }}
                                        </span>
                                        <span style="font-size:12px; color: var(--admin-muted);">
                                            {{ $t->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </div>
                                    <div style="margin-top:6px; font-weight:700; color: {{ $seatColor }};">
                                        Rp {{ number_format($t->price, 0, ',', '.') }}
                                    </div>
                                    <div style="margin-top:4px; color: var(--admin-muted); font-size:12px;">
                                        Kuota: {{ $t->quota ?? '—' }}
                                    </div>
                                    @if($t->description)
                                        <div style="margin-top:6px; color: var(--admin-muted); font-size:13px; white-space: normal; overflow-wrap:anywhere; word-break:break-word;">
                                            {{ $t->description }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Seat Map Preview --}}
                {{-- Seat Selection: semua kontrol berada di dalam kotak --}}
                <div style="margin-top:20px;">
                    <h3 style="margin:0 0 8px; font-size:16px;">Seat Selection</h3>
                    <div id="selectMapContainer" style="position:relative; border:1px solid var(--admin-border); border-radius:10px; background:#0f0f10; height:560px; overflow:auto;">
                        <div style="position:absolute; right:12px; top:12px; display:flex; gap:8px; z-index:10;">
                            <button id="selZoomOut" class="btn btn-outline btn-sm">−</button>
                            <button id="selZoomIn" class="btn btn-outline btn-sm">+</button>
                            <button id="selZoomReset" class="btn btn-secondary btn-sm">Reset</button>
                        </div>
                        <svg id="selectMapSvg" width="100%" height="100%" style="display:block;">
                            <g id="selectMapContent" transform="scale(1)" data-scale="1" style="transform-origin: 0 0;"></g>
                            <rect id="lasso" x="0" y="0" width="0" height="0" fill="rgba(59,130,246,0.2)" stroke="#3b82f6" stroke-dasharray="4 3" style="display:none;"></rect>
                        </svg>
                    </div>
                
                    {{-- Toolbar Selected diposisikan di luar seat selection, tepat di bawahnya --}}
                    <div id="selectToolbar" style="margin-top:12px; border:1px solid var(--admin-border); border-radius:8px; padding:10px 12px; display:flex; justify-content:space-between; align-items:center; gap:8px; background: rgba(0,0,0,0.25);">
                        <div>
                            <strong>Selected:</strong>
                            <span id="selectedList" style="color: var(--admin-muted);">—</span>
                        </div>
                        <div style="display:flex; gap:8px;">
                            <button id="lockSeats" class="btn btn-primary btn-sm">Lock Selected</button>
                            <button id="unlockSeats" class="btn btn-outline btn-sm">Unlock</button>
                        </div>
                    </div>
                </div>

                <script>
                    (function(){
                        var eventId = {{ $event->id }};
                        var svg = document.getElementById('selectMapSvg');
                        var content = document.getElementById('selectMapContent');
                        var container = document.getElementById('selectMapContainer');
                        var lasso = document.getElementById('lasso');
                        var selected = new Set();
                        var nameById = {}; // peta id → nama tampilan
                        var scale = 1, STEP = 0.15, MIN = 0.5, MAX = 2.5;

                        function applyScale(){
                            content.setAttribute('transform', 'scale(' + scale + ')');
                            content.dataset.scale = String(scale);
                        }
                        function updateSelectedList(){
                            var arr = Array.from(selected).map(function(id){ return nameById[id] || id; });
                            document.getElementById('selectedList').textContent = arr.length ? arr.join(', ') : '—';
                        }
                        function colorFor(n){
                            if (n.disabled) return '#fca5a5';
                            if (n.locked_by_me) return '#22c55e';
                            if (n.locked) return '#ef4444';
                            var t = n.ticket_type_name || '';
                            if (t==='VIP') return '#ef4444';
                            if (t==='Gold') return '#f59e0b';
                            if (t==='Reguler') return '#3b82f6';
                            return '#e5e7eb';
                        }
                        function draw(layout){
                            content.innerHTML = '';
                            nameById = {};
                            if (!layout || !layout.length) {
                                var msg = document.createElementNS('http://www.w3.org/2000/svg','text');
                                msg.setAttribute('x', 16);
                                msg.setAttribute('y', 24);
                                msg.setAttribute('fill', '#9ca3af');
                                msg.textContent = 'Seat map belum tersedia';
                                content.appendChild(msg);
                                return;
                            }

                            // Bounding box untuk scroll 4 arah
                            var maxX = 0, maxY = 0;
                            layout.forEach(function(n){
                                maxX = Math.max(maxX, n.x + n.w);
                                maxY = Math.max(maxY, n.y + n.h);
                            });
                            var PAD = 60;
                            var svgW = Math.max(800, Math.ceil(maxX + PAD));
                            var svgH = Math.max(600, Math.ceil(maxY + PAD));
                            svg.setAttribute('width', svgW);
                            svg.setAttribute('height', svgH);

                            var chairs = layout.filter(function(n){ return n.type==='chair'; });
                            var others = layout.filter(function(n){ return n.type!=='chair'; });

                            others.forEach(function(n){
                                var rect = document.createElementNS('http://www.w3.org/2000/svg','rect');
                                rect.setAttribute('x', n.x);
                                rect.setAttribute('y', n.y);
                                rect.setAttribute('width', n.w);
                                rect.setAttribute('height', n.h);
                                rect.setAttribute('rx', 12);
                                rect.setAttribute('fill', (n.type==='stage' || n.type==='talent') ? '#9ca3af' : '#1f2937');
                                rect.setAttribute('stroke', '#334155');
                                content.appendChild(rect);

                                var label = document.createElementNS('http://www.w3.org/2000/svg','text');
                                label.setAttribute('x', n.x + n.w/2);
                                label.setAttribute('y', n.y + n.h/2);
                                label.setAttribute('text-anchor', 'middle');
                                label.setAttribute('dominant-baseline', 'middle');
                                label.setAttribute('fill', '#111');
                                label.setAttribute('font-weight', '700');
                                label.textContent = n.label || (n.type==='talent' ? 'TALENT' : '');
                                content.appendChild(label);
                            });

                            chairs.forEach(function(n){
                                var r = Math.min(n.w, n.h)/2;
                                var cx = n.x + n.w/2;
                                var cy = n.y + n.h/2;
                                var c = document.createElementNS('http://www.w3.org/2000/svg','circle');
                                c.setAttribute('cx', cx);
                                c.setAttribute('cy', cy);
                                c.setAttribute('r', r);
                                c.setAttribute('fill', colorFor(n));
                                c.setAttribute('stroke', selected.has(n.id) ? '#22c55e' : '#334155');
                                c.setAttribute('data-id', n.id);
                                c.style.cursor = (n.locked && !n.locked_by_me) ? 'not-allowed' : 'pointer';
                                // simpan nama tampilan
                                nameById[n.id] = n.display_name || (n.label || 'Kursi');

                                c.addEventListener('click', function(e){
                                    var id = e.target.getAttribute('data-id');
                                    if (n.locked && !n.locked_by_me) return;
                                    if (selected.has(id)) selected.delete(id); else selected.add(id);
                                    updateSelectedList();
                                    c.setAttribute('stroke', selected.has(id) ? '#22c55e' : '#334155');
                                });
                                content.appendChild(c);
                            });
                        }
                        function fetchMap(){
                            fetch('{{ route('user.events.seat.map', $event) }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                                .then(function(r){ if (!r.ok) throw new Error('Gagal memuat'); return r.json(); })
                                .then(function(data){
                                    var locks = data.locks || {};
                                    var layout = (data.layout || []).map(function(n){
                                        var lockedBy = locks[n.id];
                                        n.locked_by_me = !!(lockedBy && lockedBy.by_me);
                                        n.locked = !!lockedBy && !lockedBy.by_me;
                                        return n;
                                    });
                                    draw(layout);
                                })
                                .catch(function(){ draw([]); });
                        }

                        // Lasso (Shift + drag)
                        var dragging = false, startX=0, startY=0;
                        svg.addEventListener('mousedown', function(e){
                            if (!e.shiftKey) return;
                            dragging = true;
                            var pt = svg.createSVGPoint(); pt.x = e.clientX; pt.y = e.clientY;
                            var s = pt.matrixTransform(svg.getScreenCTM().inverse());
                            startX = s.x; startY = s.y;
                            lasso.setAttribute('x', startX);
                            lasso.setAttribute('y', startY);
                            lasso.setAttribute('width', 0);
                            lasso.setAttribute('height', 0);
                            lasso.style.display = 'block';
                        });
                        svg.addEventListener('mousemove', function(e){
                            if (!dragging) return;
                            var pt = svg.createSVGPoint(); pt.x = e.clientX; pt.y = e.clientY;
                            var p = pt.matrixTransform(svg.getScreenCTM().inverse());
                            var x = Math.min(startX, p.x), y = Math.min(startY, p.y);
                            var w = Math.abs(p.x - startX), h = Math.abs(p.y - startY);
                            lasso.setAttribute('x', x); lasso.setAttribute('y', y);
                            lasso.setAttribute('width', w); lasso.setAttribute('height', h);
                        });
                        svg.addEventListener('mouseup', function(e){
                            if (!dragging) return;
                            dragging = false;
                            var lx = parseFloat(lasso.getAttribute('x'));
                            var ly = parseFloat(lasso.getAttribute('y'));
                            var lw = parseFloat(lasso.getAttribute('width'));
                            var lh = parseFloat(lasso.getAttribute('height'));
                            lasso.style.display = 'none';
                            Array.from(content.querySelectorAll('circle')).forEach(function(c){
                                var id = c.getAttribute('data-id');
                                var cx = parseFloat(c.getAttribute('cx'));
                                var cy = parseFloat(c.getAttribute('cy'));
                                var locked = c.style.cursor === 'not-allowed';
                                if (!locked && cx >= lx && cx <= lx+lw && cy >= ly && cy <= ly+lh) {
                                    selected.add(id);
                                    c.setAttribute('stroke', '#22c55e');
                                }
                            });
                            updateSelectedList();
                        });

                        // Zoom tombol
                        document.getElementById('selZoomIn').addEventListener('click', function(){ scale = Math.min(MAX, scale+STEP); applyScale(); });
                        document.getElementById('selZoomOut').addEventListener('click', function(){ scale = Math.max(MIN, scale-STEP); applyScale(); });
                        document.getElementById('selZoomReset').addEventListener('click', function(){ scale = 1; applyScale(); });

                        // Zoom dengan wheel (tahan Ctrl/Shift/Alt)
                        container.addEventListener('wheel', function(e){
                            if (e.ctrlKey || e.shiftKey || e.altKey) {
                                e.preventDefault();
                                scale = e.deltaY < 0 ? Math.min(MAX, scale+STEP) : Math.max(MIN, scale-STEP);
                                applyScale();
                            }
                        }, { passive: false });

                        // Lock / Unlock
                        document.getElementById('lockSeats').addEventListener('click', function(){
                            var seats = Array.from(selected);
                            if (!seats.length) return;
                            fetch('{{ route('user.events.seat.lock', $event) }}', {
                                method: 'POST',
                                headers: { 'Content-Type':'application/json', 'X-Requested-With':'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                body: JSON.stringify({ seats: seats, ttl: 120 })
                            }).then(function(r){ return r.json(); }).then(function(){
                                fetchMap();
                            });
                        });
                        document.getElementById('unlockSeats').addEventListener('click', function(){
                            var seats = Array.from(selected);
                            if (!seats.length) return;
                            fetch('{{ route('user.events.seat.unlock', $event) }}', {
                                method: 'POST',
                                headers: { 'Content-Type':'application/json', 'X-Requested-With':'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                body: JSON.stringify({ seats: seats })
                            }).then(function(r){ return r.json(); }).then(function(){
                                selected.clear();
                                updateSelectedList();
                                fetchMap();
                            });
                        });

                        applyScale();
                        fetchMap();
                    })();
                </script>
                <div style="margin-top:18px; display:flex; gap:8px;">
                    <a href="{{ route('user.events.index') }}" class="btn btn-outline">Kembali ke daftar</a>
                    <button class="btn btn-primary" disabled>Pesan Tiket (coming soon)</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection