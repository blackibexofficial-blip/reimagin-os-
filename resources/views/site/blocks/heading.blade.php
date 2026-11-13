@php
    $attrs = $block['attrs'] ?? [];
    $level = in_array($attrs['level'] ?? 'h2', ['h1', 'h2', 'h3', 'h4'], true) ? $attrs['level'] : 'h2';
    $align = ['left' => 'text-left', 'center' => 'text-center', 'right' => 'text-right'][$attrs['align'] ?? 'left'] ?? 'text-left';
    $size = ['h1' => 'text-4xl sm:text-5xl', 'h2' => 'text-3xl sm:text-4xl', 'h3' => 'text-2xl', 'h4' => 'text-xl'][$level];
@endphp
<div class="mx-auto max-w-6xl px-5">
    <{{ $level }} class="font-display font-semibold tracking-tight {{ $size }} {{ $align }}"
        data-block="heading" data-bind="text">{{ $attrs['text'] ?? '' }}</{{ $level }}>
</div>
