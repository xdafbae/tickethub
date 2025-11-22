@if ($paginator->hasPages())
@php
    $elements = \Illuminate\Pagination\UrlWindow::make($paginator);
@endphp
<div style="display:flex; align-items:center; justify-content:space-between; gap:10px;">
    <nav aria-label="Pagination">
        <div style="display:flex; gap:6px; flex-wrap:wrap;">
            {{-- Tombol Sebelumnya --}}
            @if ($paginator->onFirstPage())
                <span class="btn btn-outline" aria-disabled="true">« Sebelumnya</span>
            @else
                <a class="btn btn-secondary" href="{{ $paginator->previousPageUrl() }}" rel="prev">« Sebelumnya</a>
            @endif

            {{-- Nomor halaman --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="btn btn-outline" aria-disabled="true">…</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="btn btn-primary" aria-current="page">{{ $page }}</span>
                        @else
                            <a class="btn btn-outline" href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Tombol Berikutnya --}}
            @if ($paginator->hasMorePages())
                <a class="btn btn-secondary" href="{{ $paginator->nextPageUrl() }}" rel="next">Berikutnya »</a>
            @else
                <span class="btn btn-outline" aria-disabled="true">Berikutnya »</span>
            @endif
        </div>
    </nav>

    {{-- Ringkasan jumlah --}}
    <div style="color:var(--admin-muted); font-size:12px;">
        Menampilkan
        {{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }}
        dari {{ $paginator->total() }} hasil
    </div>
</div>
@endif