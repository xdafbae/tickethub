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
                <th>Harga</th>
                <th>Tiket Terjual</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($events as $event)
            <tr>
                <td>{{ $event->title }}</td>
                <td>{{ $event->category }}</td>
                <td>{{ \Carbon\Carbon::parse($event->date)->format('d M Y') }}</td>
                <td>{{ $event->location }}</td>
                <td>Rp {{ number_format($event->price, 0, ',', '.') }}</td>
                <td>{{ $event->tickets_sold }}/{{ $event->total_tickets }}</td>
                <td>
                    <span class="badge badge-{{ strtolower($event->status) === 'active' ? 'success' : 'warning' }}">
                        {{ $event->status }}
                    </span>
                </td>
                <td>
                    <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-secondary btn-sm">Edit</a>
                    <a href="{{ route('admin.events.show', $event) }}" class="btn btn-secondary btn-sm">View</a>
                    <form action="{{ route('admin.events.destroy', $event) }}" method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
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
