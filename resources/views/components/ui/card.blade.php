@props(['title' => null, 'description' => null, 'padding' => true])
<section {{ $attributes->merge(['class' => 'overflow-hidden rounded-xl border border-ink-200 bg-white shadow-panel']) }}>
    @if ($title)
        <header class="flex items-start justify-between gap-4 border-b border-ink-100 px-5 py-4">
            <div>
                <h2 class="text-sm font-semibold text-ink-900">{{ $title }}</h2>
                @if ($description)<p class="mt-0.5 text-xs text-ink-500">{{ $description }}</p>@endif
            </div>
            @isset($actions)<div class="shrink-0">{{ $actions }}</div>@endisset
        </header>
    @endif
    <div @class(['px-5 py-4' => $padding])>{{ $slot }}</div>
</section>
