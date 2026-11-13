@php
    $attrs = $block['attrs'] ?? [];
    $services = \App\Models\Service::query()->where('is_published', true)
        ->orderBy('position')->limit((int) ($attrs['limit'] ?? 4))->get();
@endphp
<section class="mx-auto max-w-6xl px-5 py-6" data-block="services_list">
    <h2 class="font-display text-2xl font-semibold tracking-tight text-ink-900" data-bind="heading">{{ $attrs['heading'] ?? '' }}</h2>
    <div class="mt-6 grid gap-4 sm:grid-cols-2">
        @foreach ($services as $service)
            <article class="rounded-xl border border-ink-150 bg-white p-5">
                <span class="grid size-10 place-items-center rounded-full bg-brand-50 text-brand-700">
                    <x-nav-icon :name="$service->icon ?: 'wrench'" class="size-5" />
                </span>
                <h3 class="mt-3 font-medium text-ink-900">{{ $service->title }}</h3>
                <p class="mt-1 text-sm leading-relaxed text-ink-600">{{ $service->excerpt }}</p>
            </article>
        @endforeach
    </div>
</section>
