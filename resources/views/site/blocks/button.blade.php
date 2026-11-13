@php
    $attrs = $block['attrs'] ?? [];
    $align = ['left' => 'justify-start', 'center' => 'justify-center', 'right' => 'justify-end'][$attrs['align'] ?? 'left'] ?? 'justify-start';
    $style = [
        'primary' => 'bg-ink-900 text-white hover:bg-ink-800',
        'brand' => 'bg-brand-600 text-white hover:bg-brand-700',
        'outline' => 'border border-ink-300 text-ink-800 hover:bg-ink-50',
    ][$attrs['style'] ?? 'primary'] ?? 'bg-ink-900 text-white';
@endphp
<div class="mx-auto flex max-w-6xl px-5 {{ $align }}">
    <a class="inline-flex items-center gap-2 rounded-full px-6 py-3 text-sm font-semibold transition {{ $style }}"
       data-block="button" data-bind="url" data-href="url">
        <span data-bind="label">{{ $attrs['label'] ?? 'Learn more' }}</span>
        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
    </a>
</div>
