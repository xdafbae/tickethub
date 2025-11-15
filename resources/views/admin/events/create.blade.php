@extends('layouts.app')

@section('title', 'Tambah Event Baru')
@section('page-title', 'Tambah Event Baru')

@section('content')
<div class="table-card" style="max-width: 600px;">
    <form action="{{ route('admin.events.store') }}" method="POST" class="form-event">
        @csrf
        
        <div style="padding: 30px; display: flex; flex-direction: column; gap: 20px;">
            <div class="form-group">
                <label>Judul Event <span style="color: var(--admin-danger);">*</span></label>
                <input type="text" name="title" placeholder="Masukkan judul event" required value="{{ old('title') }}" class="form-input">
                @error('title') <span style="color: var(--admin-danger); font-size: 12px;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label>Kategori <span style="color: var(--admin-danger);">*</span></label>
                <select name="category" required class="form-input">
                    <option value="">Pilih Kategori</option>
                    <option value="Music" {{ old('category') === 'Music' ? 'selected' : '' }}>Music</option>
                    <option value="Sports" {{ old('category') === 'Sports' ? 'selected' : '' }}>Sports</option>
                    <option value="Technology" {{ old('category') === 'Technology' ? 'selected' : '' }}>Technology</option>
                    <option value="Theater" {{ old('category') === 'Theater' ? 'selected' : '' }}>Theater</option>
                </select>
                @error('category') <span style="color: var(--admin-danger); font-size: 12px;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label>Tanggal Event <span style="color: var(--admin-danger);">*</span></label>
                <input type="date" name="date" required value="{{ old('date') }}" class="form-input">
                @error('date') <span style="color: var(--admin-danger); font-size: 12px;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label>Lokasi <span style="color: var(--admin-danger);">*</span></label>
                <input type="text" name="location" placeholder="Masukkan lokasi event" required value="{{ old('location') }}" class="form-input">
                @error('location') <span style="color: var(--admin-danger); font-size: 12px;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label>Harga Tiket (Rp) <span style="color: var(--admin-danger);">*</span></label>
                <input type="number" name="price" placeholder="100000" required value="{{ old('price') }}" class="form-input">
                @error('price') <span style="color: var(--admin-danger); font-size: 12px;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label>Total Tiket <span style="color: var(--admin-danger);">*</span></label>
                <input type="number" name="total_tickets" placeholder="500" required value="{{ old('total_tickets') }}" class="form-input">
                @error('total_tickets') <span style="color: var(--admin-danger); font-size: 12px;">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="description" placeholder="Masukkan deskripsi event (opsional)" rows="4" class="form-input">{{ old('description') }}</textarea>
                @error('description') <span style="color: var(--admin-danger); font-size: 12px;">{{ $message }}</span> @enderror
            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px;">
                <a href="{{ route('admin.events.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Event</button>
            </div>
        </div>
    </form>
</div>
@endsection
