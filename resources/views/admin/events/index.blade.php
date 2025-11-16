@extends('layouts.app')

@section('title', 'Manajemen Events')
@section('page-title', 'Manajemen Events')

@section('header-actions')
<a href="{{ route('admin.events.create') }}" class="btn btn-primary">+ Tambah Event</a>
@endsection

@section('content')
<div class="table-card">
    <div class="table-header">
        <h3>Daftar Semua Events</h3>
    </div>
    
    <!-- Search & Filter -->
    <div style="padding: 20px; display: flex; gap: 12px; border-bottom: 1px solid var(--admin-border);">
        <input type="text" id="searchInput" placeholder="Cari event..." class="form-control" style="flex: 1; padding: 8px 12px; border: 1px solid var(--admin-border); border-radius: 6px;">
        <select id="categoryFilter" class="form-control" style="padding: 8px 12px; border: 1px solid var(--admin-border); border-radius: 6px;">
            <option value="">Semua Kategori</option>
            <option value="Music">Music</option>
            <option value="Sports">Sports</option>
            <option value="Technology">Technology</option>
            <option value="Theater">Theater</option>
        </select>
    </div>

    <table>
        <thead>
            <tr>
                <th>Judul Event</th>
                <th>Kategori</th>
                <th>Tanggal</th>
                <th>Lokasi</th>
                <th>Kuota</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($events as $event)
            <tr>
                <td>{{ $event->title }}</td>
                <td>
                    @php
                        $catClass = match($event->category){
                            'Music' => 'chip-purple',
                            'Sports' => 'chip-yellow',
                            'Technology' => 'chip-blue',
                            'Theater' => 'chip-gray',
                            default => 'chip-gray'
                        };
                    @endphp
                    <span class="chip {{ $catClass }}">{{ $event->category }}</span>
                </td>
                <td class="text-primary">{{ \Carbon\Carbon::parse($event->date)->format('d M Y') }}</td>
                <td class="text-muted">{{ $event->location }}</td>
                <td><span class="chip chip-gray">{{ $event->quota }}</span></td>
                <td style="display:flex; gap:8px;">
                    <a href="{{ route('admin.events.edit', $event) }}" title="Edit" style="width:32px;height:32px;border-radius:8px;background:#f59e0b;display:inline-flex;align-items:center;justify-content:center;color:#fff;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                    </a>
                    <a href="{{ route('admin.events.show', $event) }}" title="Lihat" style="width:32px;height:32px;border-radius:8px;background:#3b82f6;display:inline-flex;align-items:center;justify-content:center;color:#fff;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </a>
                    <form action="{{ route('admin.events.destroy', $event) }}" method="POST" class="js-delete-form" style="display:inline;">
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
                <td colspan="8" style="text-align: center; color: var(--admin-muted); padding: 40px;">
                    Tidak ada event yang ditemukan.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
@if($events->hasPages())
<div style="margin-top: 20px;">
    {{ $events->links() }}
</div>
@endif
@endsection

@section('additional-js')
<script>
    // Search functionality
    document.getElementById('searchInput').addEventListener('keyup', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });

    // Category filter
    document.getElementById('categoryFilter').addEventListener('change', function(e) {
        const category = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const cellText = row.cells[1].textContent.toLowerCase();
            row.style.display = !category || cellText.includes(category) ? '' : 'none';
        });
    });
</script>
@endsection
