@extends('layouts.app')

@section('title', 'Orders')
@section('page-title', 'Orders')

@section('content')
<div class="table-card">
    <div class="table-header" style="display:flex; align-items:center; justify-content:space-between;">
        <h3>Daftar Order</h3>
        <form method="GET" action="{{ route('admin.orders.index') }}" style="display:flex; gap:8px;">
            <input type="text" name="q" value="{{ $q }}" placeholder="Cari ID, nama, email, external_ref" class="input">
            <button class="btn btn-secondary" type="submit">Cari</button>
        </form>
    </div>
    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Pembeli</th>
                <th>Email</th>
                <th>Event</th>
                <th>Total</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        @forelse($orders as $o)
            <tr>
                <td>{{ $loop->iteration + (($orders->firstItem() ?? 1) - 1) }}</td>
                <td>{{ $o->buyer_name }}</td>
                <td>{{ $o->buyer_email }}</td>
                <td>{{ $o->event?->title ?? '-' }}</td>
                <td>Rp {{ number_format($o->total, 0, ',', '.') }}</td>
                <td><span class="badge">{{ $o->status }}</span></td>
                <td>
                    <a href="{{ route('admin.orders.show', $o) }}" class="btn btn-sm btn-secondary">Detail</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" style="text-align:center; color:var(--admin-muted);">Tidak ada data</td></tr>
        @endforelse
        </tbody>
    </table>
    <div style="margin-top:12px;">
        @include('components.pagination', ['paginator' => $orders])
    </div>
</div>
@endsection