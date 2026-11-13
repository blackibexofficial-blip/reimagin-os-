@props(['variant' => 'primary', 'href' => null, 'icon' => null, 'type' => 'button'])
@php
$base = 'inline-flex items-center justify-center gap-2 rounded-lg px-3.5 py-2 text-sm font-semibold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 disabled:opacity-50';
$variants = [
    'primary' => 'bg-ink-900 text-white hover:bg-ink-800 focus-visible:outline-brand-600',
    'brand' => 'bg-brand-600 text-white hover:bg-brand-700 focus-visible:outline-brand-700',
    'secondary' => 'border border-ink-200 bg-white text-ink-800 hover:bg-ink-50 focus-visible:outline-ink-400',
    'ghost' => 'text-ink-600 hover:bg-ink-100 focus-visible:outline-ink-400',
    'danger' => 'bg-red-600 text-white hover:bg-red-700 focus-visible:outline-red-700',
];
$classes = $base.' '.($variants[$variant] ?? $variants['primary']);
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)<x-nav-icon :name="$icon" class="size-4" />@endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)<x-nav-icon :name="$icon" class="size-4" />@endif
        {{ $slot }}
    </button>
@endif
