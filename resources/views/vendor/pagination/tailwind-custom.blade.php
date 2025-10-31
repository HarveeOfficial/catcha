@if ($paginator->hasPages())
    <nav class="flex items-center justify-between">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="px-3 py-2 text-sm font-medium text-gray-400 cursor-not-allowed rounded">
                &larr; Previous
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded transition-colors">
                &larr; Previous
            </a>
        @endif

        {{-- Pagination Elements --}}
        <div class="flex gap-1">
            {{-- "..." if there are pages before the first visible page --}}
            @if ($paginator->currentPage() > 2)
                <span class="px-3 py-2 text-sm font-medium text-gray-500">...</span>
            @endif

            {{-- Numbered pagination links --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="px-3 py-2 text-sm font-medium text-gray-500">{{ $element }}</span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="px-3 py-2 text-sm font-medium text-white bg-indigo-600 rounded">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}" class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded transition-colors">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- "..." if there are pages after the last visible page --}}
            @if ($paginator->currentPage() < $paginator->lastPage() - 1)
                <span class="px-3 py-2 text-sm font-medium text-gray-500">...</span>
            @endif
        </div>

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded transition-colors">
                Next &rarr;
            </a>
        @else
            <span class="px-3 py-2 text-sm font-medium text-gray-400 cursor-not-allowed rounded">
                Next &rarr;
            </span>
        @endif
    </nav>
@endif
