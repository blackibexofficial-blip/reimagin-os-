@php
    $attrs = $block['attrs'] ?? [];
    $align = ['left' => 'text-left', 'center' => 'text-center', 'right' => 'text-right'][$attrs['align'] ?? 'left'] ?? 'text-left';
    $size = ['sm' => 'text-sm', 'base' => 'text-base', 'lg' => 'text-lg'][$attrs['size'] ?? 'base'] ?? 'text-base';
@endphp
<div class="mx-auto max-w-3xl px-5">
    <p class="leading-relaxed text-ink-600 {{ $size }} {{ $align }}"
       data-block="text" data-bind="body">{!! nl2br(e($attrs['body'] ?? '')) !!}</p>
</div>
