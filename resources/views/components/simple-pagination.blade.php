@if ($paginator->hasPages())
<nav aria-label="Pagination" style="display:flex; flex-direction:column; gap:8px;">
    <div style="display:flex; align-items:center; gap:10px;">
        @if ($paginator->onFirstPage())
            <span class="btn btn-outline" aria-disabled="true">« Prev</span>
        @else
            <a class="btn btn-outline" href="{{ $paginator->previousPageUrl() }}" rel="prev">« Prev</a>
        @endif

        <span class="muted">Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}</span>

        @if ($paginator->hasMorePages())
            <a class="btn btn-outline" href="{{ $paginator->nextPageUrl() }}" rel="next">Next »</a>
        @else
            <span class="btn btn-outline" aria-disabled="true">Next »</span>
        @endif
    </div>

    <ul style="display:flex; gap:6px; list-style:none; padding:0; margin:0;">
        @foreach ($elements as $element)
            @if (is_string($element))
                <li><span class="muted">{{ $element }}</span></li>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li><span class="btn btn-secondary" aria-current="page">{{ $page }}</span></li>
                    @else
                        <li><a class="btn btn-outline" href="{{ $url }}">{{ $page }}</a></li>
                    @endif
                @endforeach
            @endif
        @endforeach
    </ul>
</nav>
@endif