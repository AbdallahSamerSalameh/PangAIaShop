@if ($paginator->hasPages())
<div class="custom-pagination">
    {{-- Previous Page Link --}}
    @if ($paginator->onFirstPage())
        <span class="page-btn disabled">
            <i class="fa fa-angle-left"></i>
        </span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}" class="page-btn">
            <i class="fa fa-angle-left"></i>
        </a>
    @endif

    {{-- Pagination Elements --}}
    @foreach ($elements as $element)
        {{-- "Three Dots" Separator --}}
        @if (is_string($element))
            <span class="page-btn disabled">{{ $element }}</span>
        @endif

        {{-- Array Of Links --}}
        @if (is_array($element))
            @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                    <span class="page-btn active">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="page-btn">{{ $page }}</a>
                @endif
            @endforeach
        @endif
    @endforeach

    {{-- Next Page Link --}}
    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" class="page-btn">
            <i class="fa fa-angle-right"></i>
        </a>
    @else
        <span class="page-btn disabled">
            <i class="fa fa-angle-right"></i>
        </span>
    @endif
</div>
@endif
