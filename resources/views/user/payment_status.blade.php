@extends('layouts.user')

@section('title', 'Status Pembayaran - Order #' . $order->id)
@section('page-title', 'Status Pembayaran')

@section('content')
<div class="container" style="max-width: 900px; margin: 0 auto; padding: 16px;">
    @if(session('status'))
        <div class="status-banner status-paid">
            {{ session('status') }}
        </div>
    @endif
    @if($errors->any())
        <div class="status-banner status-failed">
            {{ implode(' ', $errors->all()) }}
        </div>
    @endif

    @php
        $status = $order->status;
        $statusMeta = [
            'paid' => ['label' => 'Berhasil', 'class' => 'status-paid'],
            'failed' => ['label' => 'Gagal', 'class' => 'status-failed'],
            'expired' => ['label' => 'Kedaluwarsa', 'class' => 'status-expired'],
            'pending' => ['label' => 'Menunggu Pembayaran', 'class' => 'status-pending'],
        ];
        $meta = $statusMeta[$status] ?? $statusMeta['pending'];
        $lastUpdated = optional($payment)->updated_at ?? $order->updated_at;
    @endphp

    <div class="status-banner {{ $meta['class'] }}">
        Status: {{ $meta['label'] }}
    </div>

    <div class="table-card">
        <div class="table-header">
            <h3>Order #{{ $order->id }}</h3>
        </div>
        <div style="padding:20px;">
            <div class="info-list">
                <div class="info-item">
                    <div class="info-label">Event</div>
                    <div class="info-value">{{ $order->event->title }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Total</div>
                    <div class="info-value">Rp {{ number_format($order->total, 0, ',', '.') }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Order ID (Midtrans)</div>
                    <div class="info-value">{{ $order->external_ref ?? '-' }}</div>
                </div>
            </div>

            <div class="info-list" style="margin-top:12px;">
                <div class="info-item">
                    <div class="info-label">Status Order</div>
                    <div class="info-value">
                        @if($order->status === 'paid')
                            <span class="chip" style="background:#22c55e;color:#fff;">Berhasil</span>
                        @elseif($order->status === 'failed')
                            <span class="chip" style="background:#ef4444;color:#fff;">Gagal</span>
                        @elseif($order->status === 'expired')
                            <span class="chip" style="background:#f59e0b;color:#fff;">Kedaluwarsa</span>
                        @else
                            <span class="chip" style="background:#3b82f6;color:#fff;">Menunggu Pembayaran</span>
                        @endif
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-label">Terakhir Diperbarui</div>
                    <div class="info-value">{{ optional($lastUpdated)->format('d M Y H:i') }}</div>
                </div>

                @if($payment)
                    <div class="info-item">
                        <div class="info-label">Metode</div>
                        <div class="info-value">{{ $payment->provider ?? 'midtrans' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Transaksi</div>
                        <div class="info-value">{{ $payment->provider_transaction_id ?? '-' }}</div>
                    </div>
                @endif
            </div>

            <div class="action-row">
                <a href="{{ route('user.events.show', ['event' => $order->event->id]) }}" class="btn btn-secondary">Kembali ke Event</a>

                <form method="POST" action="{{ route('user.payment.status.check', ['order' => $order->id]) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary">Check Status</button>
                </form>

                @if(($order->status === 'pending') && $payment && $payment->redirect_url)
                    <a href="{{ $payment->redirect_url }}" class="btn btn-success">Lanjutkan Pembayaran</a>
                @endif
            </div>

            <div class="helper-text">
                - “Berhasil”: pembayaran diterima (capture/settlement).<br>
                - “Gagal”: cancel/deny dari Midtrans.<br>
                - “Kedaluwarsa”: batas waktu pembayaran lewat.<br>
                - “Menunggu”: transaksi dibuat tapi belum ada penyelesaian.
            </div>
        </div>
    </div>
</div>
@endsection