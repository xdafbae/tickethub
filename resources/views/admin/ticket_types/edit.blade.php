@extends('layouts.app')

@section('title', 'Edit Tipe Tiket')
@section('page-title', 'Edit Tipe Tiket')

@section('header-actions')
<a href="{{ url()->previous() }}" class="btn btn-secondary">← Kembali</a>
@endsection

@section('content')
<div class="table-card" style="max-width: 600px;">
    <form action="{{ route('admin.ticket_types.update', $type) }}" method="POST" class="form-event">
        @csrf
        @method('PUT')
        <div style="padding: 30px; display: flex; flex-direction: column; gap: 20px;">
            <div class="form-group">
                <label>Event <span style="color: var(--admin-danger);">*</span></label>
                <select name="event_id" required class="form-input">
                    <option value="">Pilih Event</option>
                    @foreach($events as $ev)
                        <option value="{{ $ev->id }}" {{ old('event_id', $type->event_id) == $ev->id ? 'selected' : '' }}>{{ $ev->title }}</option>
                    @endforeach
                </select>
                @error('event_id') <span style="color: var(--admin-danger); font-size: 12px;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label>Nama Tipe <span style="color: var(--admin-danger);">*</span></label>
                <select name="name" required class="form-input">
                    <option value="">Pilih Tipe</option>
                    @foreach(['VIP','Reguler','Early-bird'] as $t)
                        <option value="{{ $t }}" {{ old('name', $type->name) === $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
                @error('name') <span style="color: var(--admin-danger); font-size: 12px;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label>Harga (Rp) <span style="color: var(--admin-danger);">*</span></label>
                <input type="text" id="priceDisplayEdit" name="price_view" placeholder="Rp 0" value="{{ number_format((int)old('price', $type->price),0,',','.') }}" class="form-input">
                <input type="hidden" name="price" id="priceRawEdit" value="{{ old('price', $type->price) }}">
                @error('price') <span style="color: var(--admin-danger); font-size: 12px;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label>Kuota <span style="color: var(--admin-danger);">*</span></label>
                <input type="number" name="quota" required value="{{ old('quota', $type->quota) }}" class="form-input">
                @error('quota') <span style="color: var(--admin-danger); font-size: 12px;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="description" rows="3" class="form-input">{{ old('description', $type->description) }}</textarea>
                @error('description') <span style="color: var(--admin-danger); font-size: 12px;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group" style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; align-items:center;">
                <div>
                    <label>Tersedia dari</label>
                    <input type="date" name="available_from" value="{{ old('available_from', optional($type->available_from)->format('Y-m-d')) }}" class="form-input">
                </div>
                <div>
                    <label>hingga</label>
                    <input type="date" name="available_to" value="{{ old('available_to', optional($type->available_to)->format('Y-m-d')) }}" class="form-input">
                </div>
            </div>


            <div class="form-group" style="display:flex; align-items:center; gap:8px;">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $type->is_active) ? 'checked' : '' }}>
                <label for="is_active">Aktif</label>
            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 10px;">
                <a href="{{ route('admin.ticket_types.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </form>
</div>
@endsection

@section('additional-js')
<script>
    (function(){
        var form = document.querySelector('form[action="{{ route('admin.ticket_types.update', $type) }}"]');
        var disp = document.getElementById('priceDisplayEdit');
        var raw = document.getElementById('priceRawEdit');
        function fmt(n){ return new Intl.NumberFormat('id-ID').format(n); }
        function digits(s){ return (s||'').replace(/[^0-9]/g,''); }
        disp.addEventListener('input', function(e){
            var d = digits(e.target.value);
            raw.value = d;
            e.target.value = d ? fmt(Number(d)) : '';
        });
        if (raw.value) disp.value = fmt(Number(raw.value));
        form.addEventListener('submit', function(){ raw.value = digits(disp.value); });
    })();
</script>
@endsection