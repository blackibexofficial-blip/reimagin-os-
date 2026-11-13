@props(['title' => 'Nothing here yet', 'description' => null, 'icon' => 'layers'])
<div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-ink-300 bg-white/60 px-6 py-14 text-center">
    <span class="grid size-12 place-items-center rounded-full bg-ink-100 text-ink-400">
        <x-nav-icon :name="$icon" class="size-6" />
    </span>
    <p class="mt-4 text-sm font-semibold text-ink-800">{{ $title }}</p>
    @if ($description)
        <p class="mt-1 max-w-sm text-sm text-ink-500">{{ $description }}</p>
    @endif
    @isset($action)
        <div class="mt-5">{{ $action }}</div>
    @endisset
</div>
