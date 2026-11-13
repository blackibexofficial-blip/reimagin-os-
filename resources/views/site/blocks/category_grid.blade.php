@php
    $attrs = $block['attrs'] ?? [];
    $categories = \App\Models\Category::query()->where('is_active', true)->orderBy('position')
        ->limit((int) ($attrs['limit'] ?? 4))->get();
@endphp
<section class="mx-auto max-w-6xl px-5 py-4" data-block="category_grid">
    <h2 class="font-display text-2xl font-semibold tracking-tight text-ink-900" data-bind="heading">{{ $attrs['heading'] ?? '' }}</h2>
    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($categories as $category)
            <a href="{{ route('site.categories.show', $category) }}"
               class="group overflow-hidden rounded-xl border border-ink-150 bg-white p-5 transition hover:border-brand-300 hover:shadow-panel">
                <span class="grid size-10 place-items-center rounded-full bg-ink-100 text-ink-600 group-hover:bg-brand-50 group-hover:text-brand-700">
                    <x-nav-icon name="box" class="size-5" />
                </span>
                <p class="mt-3 font-medium text-ink-900">{{ $category->name }}</p>
                <p class="mt-1 line-clamp-2 text-xs text-ink-500">{{ $category->description }}</p>
                <p class="mt-3 text-xs font-medium text-brand-700">{{ $category->products()->published()->count() }} pieces →</p>
            </a>
        @endforeach
    </div>
</section>
