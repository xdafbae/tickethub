@extends('layouts.app')

@section('title', 'Halaman Baru')
@section('page-title', 'Halaman Baru')

@section('header-actions')
<!-- Tambahkan action buttons di sini -->
@endsection

@section('content')
<!-- Mulai tambahkan konten di sini -->

<div class="content-area">
    <!-- Template untuk halaman baru -->
    <div class="table-card">
        <div class="table-header">
            <h3>Halaman Baru</h3>
        </div>
        
        <div style="padding: 40px; text-align: center; color: var(--admin-muted);">
            <p style="font-size: 16px; margin-bottom: 20px;">Selamat datang di halaman baru</p>
            <p style="font-size: 14px;">Mulai tambahkan konten Anda di sini dengan mengedit file resources/views/admin/blank.blade.php</p>
        </div>
    </div>
</div>

@endsection
