@extends('layouts.app')

@section('title', 'Manajemen Promo')
@section('page-title', 'Manajemen Promo')

@section('header-actions')
<a href="{{ route('admin.promos.create') }}" class="btn btn-primary">+ Tambah Promo</a>
@endsection

@section('content')
<div class="table-card">
    <div class="table-header"><h3>Daftar Promo</h3></div>
    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Tipe</th>
                <th>Nilai</th>
                <th>Limit Total</th>
                <th>Limit / User</th>
                <th>Dipakai</th>
                <th>Aktif</th>
                <th>Periode</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($promos as $p)
            <tr>
                <td><strong>{{ $p->code }}</strong></td>
                <td>{{ $p->type }}</td>
                <td>{{ $p->type === 'percent' ? $p->value.'%' : 'Rp '.number_format($p->value,0,',','.') }}</td>
                <td>{{ $p->usage_limit_total ?? '—' }}</td>
                <td>{{ $p->usage_limit_per_user ?? '—' }}</td>
                <td>{{ $p->used_count }}</td>
                <td>{!! $p->is_active ? '<span class="badge badge-success">Ya</span>' : '<span class="badge badge-danger">Tidak</span>' !!}</td>
                <td>
                    @if($p->starts_at || $p->expires_at)
                        {{ optional($p->starts_at)->format('d M Y') }} - {{ optional($p->expires_at)->format('d M Y') }}
                    @else
                        —
                    @endif
                </td>
                <td style="display:flex; gap:8px;">
                    <a href="{{ route('admin.promos.edit', $p) }}" class="btn btn-secondary btn-sm">Edit</a>
                    <form action="{{ route('admin.promos.destroy', $p) }}" method="POST" onsubmit="return confirm('Hapus promo ini?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger btn-sm">Hapus</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="9" style="text-align:center;color:var(--admin-muted);">Belum ada promo</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="margin-top:12px;">{{ $promos->links() }}</div>
</div>
@endsection