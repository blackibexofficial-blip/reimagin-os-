@php
    $attrs = $block['attrs'] ?? [];
    $padding = ['none' => '', 'sm' => 'py-8', 'md' => 'py-14', 'lg' => 'py-24'][$attrs['padding'] ?? 'md'] ?? 'py-14';
    $background = ['white' => 'bg-white', 'muted' => 'bg-ink-50', 'dark' => 'bg-ink-950 text-white', 'brand' => 'bg-brand-700 text-white'][$attrs['background'] ?? 'white'] ?? 'bg-white';
    $width = ['container' => 'mx-auto max-w-6xl', 'wide' => 'mx-auto max-w-7xl', 'full' => 'w-full'][$attrs['max_width'] ?? 'container'] ?? 'mx-auto max-w-6xl';
@endphp
<section class="{{ trim($background.' '.$padding) }}" data-block="section">
    <div class="{{ $width }} px-5">
        @if (! empty($slot))
            {{ $slot }}
        @elseif (empty($preview))
            <p class="text-sm text-ink-400">Empty section — add blocks inside it.</p>
        @else
            <div class="flex min-h-24 items-center justify-center rounded-lg border border-dashed border-current/20 py-6 text-xs opacity-50">
                Section — drop blocks here
            </div>
        @endif
    </div>
</section>
