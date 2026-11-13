@php
    $attrs = $block['attrs'] ?? [];
    $count = (int) ($attrs['count'] ?? 2);
    $gap = ['sm' => 'gap-4', 'md' => 'gap-8', 'lg' => 'gap-12'][$attrs['gap'] ?? 'md'] ?? 'gap-8';
    $grid = ['2' => 'sm:grid-cols-2', '3' => 'sm:grid-cols-2 lg:grid-cols-3', '4' => 'sm:grid-cols-2 lg:grid-cols-4'][(string) $count] ?? 'sm:grid-cols-2';
@endphp
<div class="mx-auto max-w-6xl px-5" data-block="columns">
    <div class="grid {{ $grid }} {{ $gap }}">
        @if (! empty($slot))
            {{ $slot }}
        @elseif (empty($preview))
            @for ($i = 0; $i < $count; $i++)
                <div class="rounded-lg border border-dashed border-ink-300 p-6 text-center text-xs text-ink-400">Column {{ $i + 1 }}</div>
            @endfor
        @else
            @for ($i = 0; $i < $count; $i++)
                <div class="rounded-lg border border-dashed border-current/20 p-6 text-center text-xs opacity-40">Column</div>
            @endfor
        @endif
    </div>
</div>
