@extends('layouts.user')

@section('title', $event->title)
@section('page-title', 'Detail Event')

@section('content')
<div class="container" style="max-width: 900px; margin: 0 auto; padding: 16px;">
    <div class="table-card" style="padding: 16px;">
        <div style="display:flex; gap:18px; align-items:flex-start; flex-wrap:wrap;">
            <div style="flex: 0 0 320px; max-width: 100%;">
                <div style="border:1px solid var(--admin-border); border-radius:12px; overflow:hidden; background:#111; height:220px;">
                    @if($event->poster)
                        <img src="{{ asset('storage/'.$event->poster) }}" alt="{{ $event->title }}" style="width:100%; height:100%; object-fit:cover;">
                    @else
                        <img src="https://picsum.photos/seed/{{ $event->id }}/800/480" alt="{{ $event->title }}" style="width:100%; height:100%; object-fit:cover;">
                    @endif
                </div>
            </div>
            <div style="flex:1;">
                <h2 style="margin:0 0 8px;">{{ $event->title }}</h2>
                <div style="display:flex; gap:8px; align-items:center; margin-bottom:8px;">
                    <span class="chip chip-gray">{{ $event->category }}</span>
                    <span style="color: var(--admin-muted);">{{ $event->date?->format('d M Y') }}</span>
                </div>
                <p style="margin: 8px 0; color: var(--admin-muted);">Lokasi: {{ $event->location }}</p>
                @if($event->quota)
                    <p style="margin: 4px 0; color: var(--admin-muted);">Kuota: {{ $event->quota }}</p>
                @endif
                @if($event->description)
                    <div style="margin-top:12px;">
                        {!! nl2br(e($event->description)) !!}
                    </div>
                @endif
                <div style="margin-top:16px; display:flex; gap:8px;">
                    <a href="{{ route('user.events.index') }}" class="btn btn-outline">Kembali ke daftar</a>
                    <button class="btn btn-primary" disabled>Pesan Tiket (coming soon)</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection