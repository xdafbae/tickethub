@extends('layouts.app')

@section('title', 'Edit Promo')
@section('page-title', 'Edit Promo')

@section('header-actions')
<a href="{{ route('admin.promos.index') }}" class="btn btn-secondary">← Kembali</a>
@endsection

@section('content')
<div class="table-card" style="max-width: 600px;">
    <form action="{{ route('admin.promos.update', $promo) }}" method="POST" class="form-event">
        @csrf
        @method('PUT')
        <div style="padding: 24px; display:flex; flex-direction:column; gap:14px;">
            <div class="form-group">
                <label>Kode</label>
                <input type="text" name="code" value="{{ old('code', $promo->code) }}" class="form-input">
            </div>
            <div class="form-group">
                <label>Tipe</label>
                <select name="type" class="form-input">
                    <option value="percent" {{ old('type', $promo->type)==='percent'?'selected':'' }}>Percent (%)</option>
                    <option value="nominal" {{ old('type', $promo->type)==='nominal'?'selected':'' }}>Nominal (Rp)</option>
                </select>
            </div>
            <div class="form-group">
                <label>Nilai</label>
                <input type="number" name="value" value="{{ old('value', $promo->value) }}" class="form-input" min="1">
            </div>
            <div class="form-group">
                <label>Limit Penggunaan (Total)</label>
                <input type="number" name="usage_limit_total" value="{{ old('usage_limit_total', $promo->usage_limit_total) }}" class="form-input" min="0">
            </div>
            <div class="form-group">
                <label>Limit Penggunaan per User</label>
                <input type="number" name="usage_limit_per_user" value="{{ old('usage_limit_per_user', $promo->usage_limit_per_user) }}" class="form-input" min="0">
            </div>
            <div class="form-group">
                <label>Periode Aktif</label>
                <div style="display:flex; gap:8px;">
                    <input type="date" name="starts_at" value="{{ old('starts_at', optional($promo->starts_at)->format('Y-m-d')) }}" class="form-input">
                    <input type="date" name="expires_at" value="{{ old('expires_at', optional($promo->expires_at)->format('Y-m-d')) }}" class="form-input">
                </div>
            </div>
            <div class="form-group checkbox">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $promo->is_active) ? 'checked' : '' }}>
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