@extends('layouts.app')

@section('title', 'Seat Map Builder')
@section('page-title', 'Seat Map Builder')

@section('header-actions')
<a href="{{ route('admin.events.show', $event) }}" class="btn btn-secondary">← Kembali</a>
@endsection

@section('content')
<div class="table-card" style="display:grid; grid-template-columns: 300px 1fr; gap: 20px;">
    <div style="padding: 20px; border-right:1px solid var(--admin-border);">
        <div class="form-group">
            <label>Nama Layout</label>
            <input type="text" id="mapName" class="form-input" value="{{ $seatMap->name }}">
        </div>
        <div class="form-group">
            <label>Tipe Tiket Aktif</label>
            <div style="display:flex; gap:8px; align-items:center;">
                <select id="typeSelect" class="form-input" style="flex:1;">
                    <option value="">— Pilih Tipe —</option>
                    @foreach(($event->ticketTypes ?? []) as $t)
                        <option value="{{ $t->id }}" data-name="{{ $t->name }}">{{ $t->name }}</option>
                    @endforeach
                </select>
                <label class="form-group checkbox" style="margin:0;">
                    <input type="checkbox" id="assignMode">
                    <span>Mode assign</span>
                </label>
            </div>
            <div style="display:flex; gap:8px; margin-top:8px;">
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
            </div>
        </div>
        <div class="form-group">
            <label>Assets tersedia</label>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <button class="btn btn-secondary asset-add" data-type="stage">Stage</button>
                <button class="btn btn-secondary asset-add" data-type="talent">Talent</button>
                <button class="btn btn-secondary asset-add" data-type="table4">Meja 4 kursi</button>
                <button class="btn btn-secondary asset-add" data-type="table6">Meja 6 kursi</button>
                <button class="btn btn-secondary asset-add" data-type="table">Meja</button>
                <button class="btn btn-secondary asset-add" data-type="chair">Kursi</button>
            </div>
        </div>
        <div id="sectionsList" style="display:none;"></div>
        <form id="saveForm" action="{{ route('admin.seat_map.save', $event) }}" method="POST" style="margin-top:20px;">
            @csrf
            <input type="hidden" name="name" id="nameInput">
            <input type="hidden" name="layout" id="layoutInput">
            <button type="submit" class="btn btn-primary">Simpan Layout</button>
        </form>
    </div>
    <div>
        <div class="table-header" style="justify-content:space-between; align-items:center;">
            <h3>Canvas</h3>
            <button id="exportImg" class="btn btn-primary btn-sm">Unduh Gambar Layout</button>
        </div>
        <div id="canvas" style="padding:20px; position:relative; height:720px; background:#fff; border:1px solid var(--admin-border); border-radius:10px; background-image: linear-gradient(#f3f4f6 1px, transparent 1px), linear-gradient(90deg, #f3f4f6 1px, transparent 1px); background-size:20px 20px;"></div>
        <div style="padding:20px; display:flex; gap:16px; align-items:center; flex-wrap:wrap;">
            <span class="badge" style="background:#3b82f6;">Reguler</span>
            <span class="badge" style="background:#f59e0b;">Gold</span>
            <span class="badge" style="background:#ef4444;">VIP</span>
            <span class="badge" style="background:#f59e0b;">Dipilih</span>
            <span class="badge" style="background:#ef4444;">Tidak Tersedia</span>
        </div>
    </div>
</div>
@endsection

@section('additional-js')
<script src="https://cdn.jsdelivr.net/npm/interactjs/dist/interact.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script>
    (function(){
        // State berupa daftar node bebas (stage/talent/table) di canvas
        var loadedLayout = {!! json_encode($seatMap->layout ?: []) !!} || [];
        var state = Array.isArray(loadedLayout) && loadedLayout.length && loadedLayout[0] && loadedLayout[0].type ? loadedLayout : [];

        var mapNameEl   = document.getElementById('mapName');
        var layoutInput = document.getElementById('layoutInput');
        var nameInput   = document.getElementById('nameInput');
        var canvas      = document.getElementById('canvas');
        var exportBtn   = document.getElementById('exportImg');
        var typeSelect  = document.getElementById('typeSelect');
        var assignMode  = document.getElementById('assignMode');
        var currentTypeId = null;
        var selectedId = null; // asset yang sedang dipilih

        var types = {!! json_encode(($event->ticketTypes ?? collect())->map(fn($t)=>['id'=>$t->id,'name'=>$t->name])->values()) !!};
        function typeById(id){ return types.find(function(x){ return String(x.id)===String(id); }); }
        if (typeSelect) typeSelect.addEventListener('change', function(){ currentTypeId = this.value || null; });

        function uid(){ return 'n'+Math.random().toString(36).slice(2,9); }
        function nextTableIndex(){
            return state.filter(function(n){ return (n.type||'').indexOf('table')===0; }).length + 1;
        }
        function colorFor(node){
            if (node.type==='stage' || node.type==='talent') return '#9ca3af';
            if (node.disabled) return '#fca5a5';
            var tt = node.ticket_type_id ? typeById(node.ticket_type_id) : null;
            if (tt){
                if (tt.name==='VIP') return '#ef4444';
                if (tt.name==='Gold') return '#f59e0b';
                if (tt.name==='Reguler') return '#3b82f6';
            }
            return '#e5e7eb';
        }
        function addAsset(type){
            var node = { id:uid(), type:type, x:40, y:40, disabled:false };
            if (type==='stage'){ node.label='STAGE'; node.w=320; node.h=140; }
            else if (type==='talent'){ node.label='TALENT'; node.w=140; node.h=60; }
            else if (type==='table4'){ node.label='T'+nextTableIndex(); node.seats=4; node.w=110; node.h=80; }
            else if (type==='table6'){ node.label='T'+nextTableIndex(); node.seats=6; node.w=120; node.h=90; }
            // Baru: meja generic dan kursi tunggal
            else if (type==='table'){ node.label='T'+nextTableIndex(); node.w=100; node.h=70; }
            else if (type==='chair'){ node.label=''; node.w=28; node.h=28; }
            state.push(node);
            render();
        }

        function render(){
            canvas.innerHTML = '';
            state.forEach(function(n){
                var el = document.createElement('div');
                el.className = 'asset-box';
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
                el.dataset.id = n.id;
                el.dataset.x = n.x||0;
                el.dataset.y = n.y||0;
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
                var tools = document.createElement('div');
                tools.style.position='absolute';
                tools.style.top='6px';
                tools.style.right='6px';
                tools.style.display='none'; // default: sembunyikan
                tools.style.gap='6px';
                var del = document.createElement('button');
                del.type = 'button';
                del.className = 'btn btn-danger btn-xs';
                del.textContent = '×';
                del.addEventListener('click', function(e){
                    e.stopPropagation();
                    state = state.filter(function(x){ return x.id !== n.id; });
                    selectedId = null;
                    render();
                });
                tools.appendChild(del);
                el.appendChild(tools);

                tools.style.display = (selectedId === n.id) ? 'flex' : 'none';

                el.addEventListener('click', function(e){
                    e.stopPropagation(); // cegah canvas click handler
                    selectedId = n.id; // pilih asset dan tampilkan tombol silang
                    if (assignMode && assignMode.checked && currentTypeId){
                        n.ticket_type_id = currentTypeId;
                        n.disabled = false;
                    } else {
                        n.disabled = !n.disabled;
                    }
                    render();
                });

                canvas.appendChild(el);
            });

            initDrag();
        }

        function initDrag(){
            interact('.asset-box').draggable({
                listeners: {
                    move: function (event) {
                        var target = event.target;
                        var x = (parseFloat(target.dataset.x) || 0) + event.dx;
                        var y = (parseFloat(target.dataset.y) || 0) + event.dy;
                        target.dataset.x = x;
                        target.dataset.y = y;
                        target.style.transform = 'translate('+x+'px,'+y+'px)';
                    },
                    end: function (event) {
                        var id = event.target.dataset.id;
                        var node = state.find(function(n){ return n.id===id; });
                        if (node){
                            node.x = parseFloat(event.target.dataset.x) || 0;
                            node.y = parseFloat(event.target.dataset.y) || 0;
                        }
                    }
                },
                modifiers: [
                    interact.modifiers.snap({
                        targets: [ interact.snappers.grid({ x: 20, y: 20 }) ],
                        range: 10,
                        relativePoints: [ { x: 0, y: 0 } ]
                    }),
                    interact.modifiers.restrictRect({
                        restriction: canvas,
                        endOnly: true
                    })
                ],
                inertia: true
            });
        }

        document.querySelectorAll('.asset-add').forEach(function(btn){
            btn.addEventListener('click', function(){ addAsset(this.dataset.type); });
        });

        document.getElementById('saveForm').addEventListener('submit', function(){
            nameInput.value = mapNameEl.value || 'Default';
            layoutInput.value = JSON.stringify(state);
        });

        exportBtn.addEventListener('click', function(){
            html2canvas(canvas, {backgroundColor:'#ffffff', scale:2}).then(function(cnv){
                var link = document.createElement('a');
                link.download = 'seatmap_event_{{ $event->id }}.png';
                link.href = cnv.toDataURL('image/png');
                link.click();
            });
        });

        // Render awal
        render();

        // Hapus seleksi saat klik di area kosong canvas
        canvas.addEventListener('click', function(){
            selectedId = null;
            render();
        });
    })();
</script>
@endsection