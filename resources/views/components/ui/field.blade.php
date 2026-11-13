@props(['label', 'name', 'hint' => null, 'required' => false])
<div {{ $attributes->only('class') }}>
    <label for="{{ $name }}" class="block text-sm font-medium text-ink-800">
        {{ $label }}@if ($required)<span class="text-red-600"> *</span>@endif
    </label>
    {{ $slot }}
    @if ($hint)<p class="mt-1 text-xs text-ink-500">{{ $hint }}</p>@endif
    @error($name)<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
</div>
