@props(['tone' => 'neutral'])
@php
$tones = [
    'neutral' => 'bg-ink-100 text-ink-700 ring-ink-200',
    'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    'warning' => 'bg-amber-50 text-amber-800 ring-amber-200',
    'danger' => 'bg-red-50 text-red-700 ring-red-200',
    'info' => 'bg-sky-50 text-sky-700 ring-sky-200',
    'brand' => 'bg-brand-50 text-brand-800 ring-brand-200',
];
@endphp
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-medium ring-1 ring-inset '.($tones[$tone] ?? $tones['neutral'])]) }}>
    {{ $slot }}
</span>
