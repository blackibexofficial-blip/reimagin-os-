@props(['items' => [], 'valueKey' => 'count', 'format' => null])
@php $max = max(1, collect($items)->max($valueKey) ?? 1); @endphp
<ul class="space-y-2.5">
    @foreach ($items as $label => $item)
        @php $value = is_array($item) ? ($item[$valueKey] ?? 0) : $item; @endphp
        <li>
            <div class="flex items-baseline justify-between gap-3 text-xs">
                <span class="font-medium text-ink-700">{{ is_array($item) && isset($item['name']) ? $item['name'] : $label }}</span>
                <span class="tabular-nums text-ink-500">{{ $format ? $format($value) : $value }}</span>
            </div>
            <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-ink-100">
                <div class="h-full rounded-full bg-brand-500" style="width: {{ max(2, round($value / $max * 100)) }}%"></div>
            </div>
        </li>
    @endforeach
</ul>
