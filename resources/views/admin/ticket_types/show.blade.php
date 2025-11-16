@extends('layouts.app')

@section('title', 'Detail Tipe Tiket')
@section('page-title', 'Detail Tipe Tiket')

@section('header-actions')
<a href="{{ url()->previous() }}" class="btn btn-secondary">← Kembali</a>
@endsection

@section('content')
<div class="table-card">
    <div class="table-header" style="justify-content: space-between;">
        <h3>{{ $type->name }} • {{ $type->event->title }}</h3>
        <a href="{{ route('admin.ticket_types.edit', $type) }}" class="btn btn-secondary btn-sm">Edit</a>
    </div>
    <div style="padding: 24px; display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
        <div>
            <p><strong>Harga:</strong> Rp {{ number_format($type->price,0,',','.') }}</p>
            <p><strong>Kuota:</strong> {{ $type->quota }}</p>
            <p><strong>Aktif:</strong> {{ $type->is_active ? 'Ya' : 'Tidak' }}</p>
        </div>
        <div>
            <p><strong>Periode Tersedia:</strong>
                @if($type->available_from)
                    {{ optional($type->available_from)->format('d M Y') }} - {{ optional($type->available_to)->format('d M Y') }}
                @else
                    —
                @endif
            </p>
            <p><strong>Deskripsi:</strong></p>
            <p>{{ $type->description }}</p>
        </div>
    </div>
</div>
@endsection