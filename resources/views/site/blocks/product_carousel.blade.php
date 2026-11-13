@php
    $attrs = $block['attrs'] ?? [];
    $products = \App\Models\Product::query()->published()->featured()->with('featuredImage')
        ->orderByDesc('is_featured')->limit((int) ($attrs['limit'] ?? 6))->get();
@endphp
<section class="mx-auto max-w-6xl px-5 py-4" data-block="product_carousel" x-data="{ scroll: 0 }">
    <div class="flex items-end justify-between gap-4">
        <h2 class="font-display text-2xl font-semibold tracking-tight text-ink-900" data-bind="heading">{{ $attrs['heading'] ?? '' }}</h2>
        <div class="flex gap-1">
            <button type="button" @click="$refs.track.scrollBy({ left: -320, behavior: 'smooth' })"
                    class="rounded-full border border-ink-200 p-1.5 text-ink-500 hover:bg-ink-50" aria-label="Previous">
                <x-nav-icon name="chevron-right" class="size-4 rotate-180" />
            </button>
            <button type="button" @click="$refs.track.scrollBy({ left: 320, behavior: 'smooth' })"
                    class="rounded-full border border-ink-200 p-1.5 text-ink-500 hover:bg-ink-50" aria-label="Next">
                <x-nav-icon name="chevron-right" class="size-4" />
            </button>
        </div>
    </div>
    <div x-ref="track" class="mt-6 flex snap-x snap-mandatory gap-5 overflow-x-auto pb-3">
        @foreach ($products as $product)
            <div class="w-64 shrink-0 snap-start">
                <x-site.product-card :product="$product" />
            </div>
        @endforeach
    </div>
</section>
