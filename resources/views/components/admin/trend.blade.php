@props(['series' => [], 'height' => 56])
@php
    $values = collect($series)->pluck('total_minor')->map(fn ($v) => (int) $v);
    $max = max(1, $values->max() ?? 1);
    $count = max(1, $values->count());
    $points = $values->values()->map(function ($value, $index) use ($max, $count) {
        $x = $count === 1 ? 0 : ($index / ($count - 1)) * 100;
        $y = 100 - ($value / $max) * 88 - 6;
        return round($x, 2).','.round($y, 2);
    })->implode(' ');
    $total = $values->sum();
@endphp
<div {{ $attributes }}>
    @if ($total === 0)
        <div class="flex items-center justify-center rounded-lg border border-dashed border-ink-200 text-xs text-ink-400" style="height: {{ $height }}px">
            No revenue in this period
        </div>
    @else
        <svg viewBox="0 0 100 100" preserveAspectRatio="none" style="height: {{ $height }}px" class="w-full" role="img"
             aria-label="Revenue trend for the selected period">
            <defs>
                <linearGradient id="trend-fill" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="var(--color-brand-500)" stop-opacity="0.28" />
                    <stop offset="100%" stop-color="var(--color-brand-500)" stop-opacity="0" />
                </linearGradient>
            </defs>
            <polygon points="0,100 {{ $points }} 100,100" fill="url(#trend-fill)" />
            <polyline points="{{ $points }}" fill="none" stroke="var(--color-brand-600)" stroke-width="1.6"
                      vector-effect="non-scaling-stroke" stroke-linejoin="round" stroke-linecap="round" />
        </svg>
    @endif
</div>
