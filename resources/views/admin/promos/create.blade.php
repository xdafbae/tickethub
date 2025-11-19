@extends('layouts.app')

@section('title', 'Tambah Promo')
@section('page-title', 'Tambah Promo')

@section('header-actions')
<a href="{{ route('admin.promos.index') }}" class="btn btn-secondary">← Kembali</a>
@endsection

@section('content')
<div class="table-card" style="max-width: 600px;">
    <form action="{{ route('admin.promos.store') }}" method="POST" class="form-event">
        @csrf
        <div style="padding: 24px; display:flex; flex-direction:column; gap:14px;">
            <div class="form-group">
                <label>Kode</label>
                <input type="text" name="code" value="{{ old('code') }}" class="form-input" placeholder="PROMO10">
            </div>
            <div class="form-group">
                <label>Tipe</label>
                <select name="type" class="form-input">
                    <option value="percent" {{ old('type')==='percent'?'selected':'' }}>Percent (%)</option>
                    <option value="nominal" {{ old('type')==='nominal'?'selected':'' }}>Nominal (Rp)</option>
                </select>
            </div>
            <div class="form-group">
                <label>Nilai</label>
                <input type="number" name="value" value="{{ old('value') }}" class="form-input" min="1">
            </div>
            <div class="form-group">
                <label>Limit Penggunaan (Total)</label>
                <input type="number" name="usage_limit_total" value="{{ old('usage_limit_total') }}" class="form-input" min="0" placeholder="Kosongkan untuk tanpa batas">
            </div>
            <div class="form-group">
                <label>Limit Penggunaan per User</label>
                <input type="number" name="usage_limit_per_user" value="{{ old('usage_limit_per_user') }}" class="form-input" min="0" placeholder="Kosongkan untuk tanpa batas">
            </div>
            <div class="form-group">
                <label>Periode Aktif</label>
                <div style="display:flex; gap:8px;">
                    <input type="date" name="starts_at" value="{{ old('starts_at') }}" class="form-input">
                    <input type="date" name="expires_at" value="{{ old('expires_at') }}" class="form-input">
                </div>
            </div>
            <div class="form-group checkbox">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
                <span>Aktif</span>
            </div>
            <div style="display:flex; gap:8px;">
                <button class="btn btn-primary">Simpan</button>
                <a href="{{ route('admin.promos.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </div>
    </form>
</div>
@endsection