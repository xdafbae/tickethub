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
        <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-secondary btn-sm">Edit</a>
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
<div class="table-card" style="margin-top: 20px;">
    <div class="table-header">
        <h3>Tipe Tiket</h3>
        <a href="{{ route('admin.ticket_types.create', ['event_id' => $event->id]) }}" class="btn btn-primary btn-sm">+ Tambah Tipe</a>
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