@extends('layouts.app')

@section('title', 'Edit Event')
@section('page-title', 'Edit Event')

@section('header-actions')
<a href="{{ url()->previous() }}" class="btn btn-secondary">← Kembali</a>
@endsection

@section('content')
<div class="table-card" style="max-width: 600px;">
    <form action="{{ route('admin.events.update', $event) }}" method="POST" class="form-event" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div style="padding: 30px; display: flex; flex-direction: column; gap: 20px;">
            <div class="form-group">
                <label>Judul Event <span style="color: var(--admin-danger);">*</span></label>
                <input type="text" name="title" required value="{{ old('title', $event->title) }}" class="form-input">
                @error('title') <span style="color: var(--admin-danger); font-size: 12px;">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label>Kategori <span style="color: var(--admin-danger);">*</span></label>
                <select name="category" required class="form-input">
                    <option value="">Pilih Kategori</option>
                    @php $cats = ['Music','Sports','Technology','Theater']; @endphp
                    @foreach($cats as $cat)
                        <option value="{{ $cat }}" {{ old('category', $event->category) === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
                @error('category') <span style="color: var(--admin-danger); font-size: 12px;">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label>Tanggal Event <span style="color: var(--admin-danger);">*</span></label>
                <input type="date" name="date" required value="{{ old('date', $event->date?->format('Y-m-d')) }}" class="form-input">
                @error('date') <span style="color: var(--admin-danger); font-size: 12px;">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label>Lokasi <span style="color: var(--admin-danger);">*</span></label>
                <input type="text" name="location" required value="{{ old('location', $event->location) }}" class="form-input">
                @error('location') <span style="color: var(--admin-danger); font-size: 12px;">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label>Kuota <span style="color: var(--admin-danger);">*</span></label>
                <input type="number" name="quota" required value="{{ old('quota', $event->quota) }}" class="form-input">
                @error('quota') <span style="color: var(--admin-danger); font-size: 12px;">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="description" rows="4" class="form-input">{{ old('description', $event->description) }}</textarea>
                @error('description') <span style="color: var(--admin-danger); font-size: 12px;">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label>Poster</label>
                <div id="posterDropEdit" style="border:2px dashed #8b5cf6;border-radius:16px;padding:28px;text-align:center;color:#7c3aed;cursor:pointer;display:flex;flex-direction:column;align-items:center;gap:8px;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    <div style="font-weight:600;">Upload File</div>
                </div>
                <input id="posterInputEdit" type="file" name="poster" accept="image/*" style="display:none;">
                <div id="posterInfoEdit" style="margin-top:12px;display:none;align-items:center;justify-content:space-between;background:#f3e8ff;color:#4c1d95;border-radius:999px;padding:10px 14px;">
                    <span id="posterNameEdit"></span>
                    <button type="button" id="posterClearEdit" style="width:28px;height:28px;border-radius:50%;background:#7c3aed;color:#fff;border:none;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                    </button>
                </div>
                @error('poster') <span style="color: var(--admin-danger); font-size: 12px;">{{ $message }}</span> @enderror
                @if($event->poster)
                    <div style="margin-top:8px;">
                        <img src="{{ asset('storage/'.$event->poster) }}" alt="Poster" style="max-width: 200px; border-radius:8px;" />
                    </div>
                @endif
            </div>
            <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px;">
                <a href="{{ route('admin.events.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </div>
    </form>
</div>
@endsection

@section('additional-js')
<script>
    (function(){
        var drop = document.getElementById('posterDropEdit');
        var input = document.getElementById('posterInputEdit');
        var info = document.getElementById('posterInfoEdit');
        var nameEl = document.getElementById('posterNameEdit');
        var clearBtn = document.getElementById('posterClearEdit');
        function showFile(f){ if(!f) return; nameEl.textContent = f.name; info.style.display = 'flex'; }
        drop.addEventListener('click', function(){ input.click(); });
        drop.addEventListener('dragover', function(e){ e.preventDefault(); drop.style.background = '#f5f3ff'; });
        drop.addEventListener('dragleave', function(){ drop.style.background = 'transparent'; });
        drop.addEventListener('drop', function(e){ e.preventDefault(); drop.style.background = 'transparent'; if(e.dataTransfer.files.length){ input.files = e.dataTransfer.files; showFile(input.files[0]); }});
        input.addEventListener('change', function(){ var f = input.files[0]; if (f) showFile(f); });
        clearBtn.addEventListener('click', function(){ input.value=''; info.style.display='none'; });
    })();
</script>
@endsection