@props(['label', 'value', 'change' => null, 'hint' => null, 'href' => null])
@if ($href)
    <a href="{{ $href }}" class="group block rounded-xl border border-ink-200 bg-white p-5 shadow-panel transition hover:border-brand-300 hover:shadow-lg">
@else
    <div class="rounded-xl border border-ink-200 bg-white p-5 shadow-panel">
@endif
    <div class="flex items-start justify-between gap-3">
        <p class="text-xs font-medium uppercase tracking-wide text-ink-500">{{ $label }}</p>
        @if ($change !== null)
            @php $positive = $change >= 0; @endphp
            <span @class([
                'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold',
                'bg-emerald-50 text-emerald-700' => $positive,
                'bg-red-50 text-red-700' => ! $positive,
            ])>{{ $positive ? '+' : '' }}{{ $change }}%</span>
        @endif
    </div>
    <p class="mt-2 font-display text-2xl font-semibold tracking-tight text-ink-900">{{ $value }}</p>
    @if ($hint)<p class="mt-1 text-xs text-ink-500">{{ $hint }}</p>@endif
@if ($href)
    </a>
@else
    </div>
@endif
