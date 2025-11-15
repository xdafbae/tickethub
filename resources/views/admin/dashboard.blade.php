@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('header-actions')
<a href="" class="btn btn-primary">+ Tambah Event</a>
@endsection

@section('content')
<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-card-title">Total Events</p>
        <p class="stat-card-value">2</p>
        <p class="stat-card-change">↑ 2 bulan ini</p>
    </div>
    <div class="stat-card">
        <p class="stat-card-title">Tiket Terjual</p>
        <p class="stat-card-value">2</p>
        <p class="stat-card-change">↑ 15% dari minggu lalu</p>
    </div>
    <div class="stat-card">
        <p class="stat-card-title">Total Revenue</p>
        <p class="stat-card-value">Rp 22</p>
        <p class="stat-card-change">↑ 22% dari bulan lalu</p>
    </div>
    <div class="stat-card">
        <p class="stat-card-title">Event Aktif</p>
        <p class="stat-card-value">2</p>
        <p class="stat-card-change">Tahun ini</p>
    </div>
</div>

<!-- Events Table -->
<div class="table-card">
    <div class="table-header">
        <h3>Event Terbaru</h3>
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
        <!-- Contoh Data 1 -->
        <tr>
            <td>Festival Musik Indie</td>
            <td>Musik</td>
            <td>21 Jan 2025</td>
            <td>Jakarta Convention Center</td>
            <td>Rp 150.000</td>
            <td>230/500</td>
            <td>
                <span class="badge badge-success">Active</span>
            </td>
            <td>
                <a href="#" class="btn btn-secondary btn-sm">Edit</a>
                <button class="btn btn-danger btn-sm" onclick="confirm('Yakin ingin menghapus?')">Hapus</button>
            </td>
        </tr>

        <!-- Contoh Data 2 -->
        <tr>
            <td>Workshop UI/UX</td>
            <td>Workshop</td>
            <td>10 Feb 2025</td>
            <td>Bandung Creative Hub</td>
            <td>Rp 200.000</td>
            <td>80/100</td>
            <td>
                <span class="badge badge-warning">Pending</span>
            </td>
            <td>
                <a href="#" class="btn btn-secondary btn-sm">Edit</a>
                <button class="btn btn-danger btn-sm" onclick="confirm('Yakin ingin menghapus?')">Hapus</button>
            </td>
        </tr>

        <!-- Jika tidak ada data -->
        <!--
        <tr>
            <td colspan="8" style="text-align: center; color: #888;">
                Belum ada event. <a href="#">Buat event baru</a>
            </td>
        </tr>
        -->
    </tbody>
</table>

