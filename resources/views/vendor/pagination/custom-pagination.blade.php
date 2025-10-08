@if ($paginator->hasPages())
    <nav>
        <ul class="pagination justify-content-center mb-0" style="list-style:none; display:flex; gap:8px; padding-left:0;">

            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="disabled" style="color:#ccc;">&lt;</li>
            @else
                <li><a href="{{ $paginator->previousPageUrl() }}" style="text-decoration:none; color:#000;">&lt;</a></li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li style="font-weight:bold; text-decoration:underline;">{{ $page }}</li>
                        @else
                            <li><a href="{{ $url }}" style="text-decoration:none; color:#000;">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li><a href="{{ $paginator->nextPageUrl() }}" style="text-decoration:none; color:#000;">&gt;</a></li>
            @else
                <li class="disabled" style="color:#ccc;">&gt;</li>
            @endif

        </ul>
    </nav>
@endif