@extends('layouts.app')

@section('title', 'Tipe Tiket')
@section('page-title', 'Tipe Tiket')

@section('header-actions')
<a href="{{ route('admin.ticket_types.create') }}" class="btn btn-primary">+ Tambah Tipe</a>
@endsection

@section('content')
<div class="table-card">
    <div class="table-header">
        <h3>Daftar Tipe Tiket</h3>
    </div>
    <table>
        <thead>
            <tr>
                <th>Event</th>
                <th>Nama</th>
                <th>Harga</th>
                <th>Kuota</th>
                <th>Aktif</th>
                <th>Tersedia</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($types as $type)
            <tr>
                <td><span class="chip chip-gray">{{ $type->event->title }}</span></td>
                <td>
                    @php
                        $chipClass = match($type->name){
                            'VIP' => 'chip-purple',
                            'Reguler' => 'chip-blue',
                            'Early-bird' => 'chip-yellow',
                            default => 'chip-gray'
                        };
                    @endphp
                    <span class="chip {{ $chipClass }}">{{ $type->name }}</span>
                </td>
                <td class="text-primary">Rp {{ number_format($type->price,0,',','.') }}</td>
                <td>{{ $type->quota }}</td>
                <td>
                    @if($type->is_active)
                        <span class="badge badge-success">Ya</span>
                    @else
                        <span class="badge badge-danger">Tidak</span>
                    @endif
                </td>
                <td>
                    @if($type->available_from)
                        {{ optional($type->available_from)->format('d M Y') }} - {{ optional($type->available_to)->format('d M Y') }}
                    @else
                        —
                    @endif
                </td>
                <td style="display:flex; gap:8px;">
                    <a href="{{ route('admin.ticket_types.edit',$type) }}" title="Edit" style="width:32px;height:32px;border-radius:8px;background:#f59e0b;display:inline-flex;align-items:center;justify-content:center;color:#fff;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                    </a>
                    <a href="{{ route('admin.ticket_types.show',$type) }}" title="Lihat" style="width:32px;height:32px;border-radius:8px;background:#3b82f6;display:inline-flex;align-items:center;justify-content:center;color:#fff;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </a>
                    <form action="{{ route('admin.ticket_types.destroy',$type) }}" method="POST" class="js-delete-form" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" title="Hapus" style="width:32px;height:32px;border-radius:8px;background:#ef4444;display:inline-flex;align-items:center;justify-content:center;color:#fff;border:none;cursor:pointer;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center; color:var(--admin-muted); padding: 40px;">Belum ada tipe tiket.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($types->hasPages())
<div style="margin-top:20px;">{{ $types->links() }}</div>
@endif
@endsection