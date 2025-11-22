@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard Saya')
@section('page-subtitle', 'Kelola tiket & lihat riwayat')

@section('content')
<div class="stat-card">
    <p class="stat-card-title">Selamat datang, {{ $user->name }}</p>
    <p class="muted">Berikut ringkasan order dan tiket Anda.</p>
</div>

<div class="table-card" style="margin-top:14px;">
    <div class="table-header">
        <h3>Order Saya</h3>
        <a href="{{ route('user.events.index') }}" class="btn btn-outline">Beli Tiket</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Event</th>
                <th>Kursi</th>
                <th>Total</th>
                <th>Status Order</th>
                <th>Status Pembayaran</th>
                <th style="width:180px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
        @forelse($orders as $o)
            @php
                $seatsCount = is_array($o->seats) ? count($o->seats) : 0;
                $paymentStatus = optional($o->payments->last())->status ?? '-';
                $canDownload = $o->status === 'paid';
            @endphp
            <tr>
                <td>#{{ $o->id }}</td>
                <td>{{ $o->event?->title ?? '-' }}</td>
                <td>{{ $seatsCount }}</td>
                <td>Rp {{ number_format($o->total, 0, ',', '.') }}</td>
                <td>
                    <span class="badge">{{ $o->status }}</span>
                </td>
                <td>
                    <span class="badge">{{ $paymentStatus }}</span>
                </td>
                <td style="display:flex; gap:8px;">
                    <a class="btn btn-secondary" href="{{ route('user.orders.show', $o) }}">Lihat</a>
                    @if($canDownload)
                        <a class="btn btn-success" href="{{ route('user.orders.ticket.download', ['order' => $o->id]) }}">Download</a>
                    @else
                        <a class="btn btn-outline" href="{{ route('user.payment.status', ['order' => $o->id]) }}">Status</a>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" style="text-align:center; color:var(--admin-muted);">Belum ada order.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <div style="margin-top:12px;">
        {{-- Gunakan template pagination kustom --}}
        {{ $orders->onEachSide(1)->links('components.simple-pagination') }}
    </div>
</div>
@endsection