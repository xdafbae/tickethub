@extends('layouts.user')

@section('title', 'Jelajahi Event')
@section('page-title', 'Jelajahi Event')

@section('content')
<div style="background: transparent;">
    <!-- Hero -->
    <section style="padding: 32px 0;">
        <div class="container" style="max-width: 1100px; margin: 0 auto; padding: 0 16px; text-align:center;">
            <h1 style="font-size: 34px; font-weight: 800; margin: 0;">
                <span style="background: linear-gradient(90deg,#22d3ee,#a78bfa); -webkit-background-clip: text; background-clip: text; color: transparent;">
                    Temukan & Beli Tiket Event Favoritmu
                </span>
            </h1>
            <p style="color: var(--admin-muted); margin-top: 10px;">
                Konser, konferensi, festival, olahraga, dan banyak lagi — semuanya di sini.
            </p>
        </div>
    </section>

    <!-- Search + Filters -->
    <section>
        <div class="container" style="max-width: 1100px; margin: 0 auto; padding: 0 16px;">
            <form method="GET" action="{{ route('user.events.index') }}" id="searchForm"
                  style="display:flex; flex-direction:column; gap:12px;">
                <div style="display:flex; gap:10px; align-items:center; justify-content:center;">
                    <input type="text" name="q" value="{{ $q }}" placeholder="Cari judul event atau lokasi..."
                           class="form-input" style="flex:1; max-width:640px;">
                    <button class="btn btn-primary" type="submit">Cari</button>
                </div>

                <div style="background: rgba(255,255,255,0.06); border:1px solid var(--admin-border);
                            border-radius:12px; padding:16px;">
                    <div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:12px;">
                        <div>
                            <label class="form-label">Kategori</label>
                            <select name="category" class="form-input">
                                <option value="">Semua</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}" {{ $category===$cat ? 'selected' : '' }}>{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Lokasi</label>
                            <input type="text" name="location" value="{{ $location }}" class="form-input" placeholder="cth: Jakarta">
                        </div>
                        <div>
                            <label class="form-label">Tanggal dari</label>
                            <input type="date" name="start" value="{{ $start }}" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Tanggal sampai</label>
                            <input type="date" name="end" value="{{ $end }}" class="form-input">
                        </div>
                    </div>

                    <div style="display:flex; gap:8px; margin-top:12px;">
                        <button class="btn btn-secondary" type="submit">Terapkan</button>
                        <a href="{{ route('user.events.index') }}" class="btn btn-outline">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <!-- Listing -->
    <section style="padding: 12px 0 24px;">
        <div class="container" style="max-width: 1100px; margin: 0 auto; padding: 0 16px;">
            <div class="table-header" style="justify-content: space-between;">
                <h3>Semua Event</h3>
                <span style="color: var(--admin-muted);">Total: {{ $events->total() }}</span>
            </div>

            @if($events->count() === 0)
                <div style="padding: 24px; text-align:center; color: var(--admin-muted);">
                    Tidak ada event ditemukan.
                </div>
            @else
            <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap: 16px; padding: 16px;">
                @foreach($events as $ev)
                    <article class="card" style="border:1px solid var(--admin-border); border-radius:12px; overflow:hidden; background: rgba(255,255,255,0.06);">
                        <div style="height:170px; background:#111;">
                            @if($ev->poster)
                                <img src="{{ asset('storage/'.$ev->poster) }}" alt="{{ $ev->title }}" style="width:100%; height:100%; object-fit:cover;">
                            @else
                                <img src="https://picsum.photos/seed/{{ $ev->id }}/600/360" alt="{{ $ev->title }}" style="width:100%; height:100%; object-fit:cover;">
                            @endif
                        </div>
                        <div style="padding: 12px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                                <span class="chip chip-gray">{{ $ev->category }}</span>
                                <span style="color: var(--admin-muted); font-size:12px;">{{ $ev->date?->format('d M Y') }}</span>
                            </div>
                            <h4 style="margin:0; font-size:16px;">{{ $ev->title }}</h4>
                            <p style="color: var(--admin-muted); font-size:13px; margin-top:6px;">Lokasi: {{ $ev->location }}</p>
                            <div style="display:flex; justify-content:flex-end; margin-top:10px;">
                                <a href="{{ route('user.events.show', $ev) }}" class="btn btn-primary btn-sm">Lihat Detail</a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <!-- Pagination -->
            <div style="padding: 12px;">
                {{ $events->links() }}
            </div>
            @endif
        </div>
    </section>
</div>
@endsection