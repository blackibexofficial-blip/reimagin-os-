@props(['name' => 'q', 'value' => null, 'placeholder' => 'Search…'])
<form method="GET" {{ $attributes->merge(['class' => 'relative']) }}>
    @foreach (request()->except([$name, 'page']) as $key => $val)
        @if (is_scalar($val))<input type="hidden" name="{{ $key }}" value="{{ $val }}">@endif
    @endforeach
    <x-nav-icon name="search" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-ink-400" />
    <input type="search" name="{{ $name }}" value="{{ $value ?? request($name) }}"
           placeholder="{{ $placeholder }}"
           class="w-full rounded-lg border-ink-200 bg-white py-2 pl-9 pr-3 text-sm placeholder-ink-400 focus:border-brand-400 focus:ring-brand-400 sm:w-64">
</form>
