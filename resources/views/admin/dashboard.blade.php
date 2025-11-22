@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('header-actions')
@role('admin')
<a href="" class="btn btn-primary">+ Tambah Event</a>
@endrole
@endsection

@section('content')
<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-card-title">Total Events</p>
        <p class="stat-card-value">2</p>
        <p class="stat-card-change">↑ 2 bulan ini</p>
    </div>
    <div class="stat-card">
        <p class="stat-card-title">Tiket Terjual</p>
        <p class="stat-card-value">730</p>
        <p class="stat-card-change">↑ 15% dari minggu lalu</p>
    </div>
    <div class="stat-card">
        <p class="stat-card-title">Total Revenue</p>
        <p class="stat-card-value">Rp 137.5M</p>
        <p class="stat-card-change">↑ 22% dari bulan lalu</p>
    </div>
    <div class="stat-card">
        <p class="stat-card-title">Event Aktif</p>
        <p class="stat-card-value">0</p>
        <p class="stat-card-change">Tahun ini</p>
    </div>
    </div>

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
                <tr>
                    <td>Summer Music Festival</td>
                    <td>Music</td>
                    <td>15/7/2024</td>
                    <td>Jakarta</td>
                    <td>Rp 150.000</td>
                    <td>450/500</td>
                    <td><span class="badge badge-success">Active</span></td>
                    <td>
                        <a href="#" class="btn btn-secondary btn-sm">Edit</a>
                        <button class="btn btn-danger btn-sm" onclick="confirm('Yakin ingin menghapus?')">Hapus</button>
                    </td>
                </tr>
                <tr>
                    <td>Tech Conference 2024</td>
                    <td>Technology</td>
                    <td>20/8/2024</td>
                    <td>Surabaya</td>
                    <td>Rp 250.000</td>
                    <td>280/300</td>
                    <td><span class="badge badge-success">Active</span></td>
                    <td>
                        <a href="#" class="btn btn-secondary btn-sm">Edit</a>
                        <button class="btn btn-danger btn-sm" onclick="confirm('Yakin ingin menghapus?')">Hapus</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
@endsection
