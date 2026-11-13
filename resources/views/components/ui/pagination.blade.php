@props(['paginator'])
@if ($paginator->hasPages())
    <nav class="mt-6 flex flex-wrap items-center justify-between gap-4" aria-label="Pagination">
        <p class="text-xs text-ink-500">
            Showing <span class="font-medium text-ink-700">{{ $paginator->firstItem() }}</span>–<span class="font-medium text-ink-700">{{ $paginator->lastItem() }}</span>
            of <span class="font-medium text-ink-700">{{ $paginator->total() }}</span>
        </p>
        <div class="flex items-center gap-1">
            @if ($paginator->onFirstPage())
                <span class="rounded-lg px-3 py-1.5 text-sm text-ink-300">Previous</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="rounded-lg px-3 py-1.5 text-sm text-ink-700 hover:bg-ink-100">Previous</a>
            @endif
            <span class="px-2 text-xs text-ink-500">Page {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="rounded-lg px-3 py-1.5 text-sm text-ink-700 hover:bg-ink-100">Next</a>
            @else
                <span class="rounded-lg px-3 py-1.5 text-sm text-ink-300">Next</span>
            @endif
        </div>
    </nav>
@endif
