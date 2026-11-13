@props(['product'])
<a href="{{ route('site.products.show', $product) }}" class="group flex flex-col overflow-hidden rounded-xl border border-ink-150 bg-white transition hover:border-brand-300 hover:shadow-panel">
    <div class="overflow-hidden bg-ink-100">
        @if ($product->featuredImage)
            <img src="{{ $product->featuredImage->url() }}" alt="{{ $product->featuredImage->alt_text ?: $product->name }}"
                 class="aspect-[4/3] w-full object-cover transition duration-300 group-hover:scale-[1.03]" loading="lazy">
        @else
            <div class="aspect-[4/3] w-full bg-gradient-to-br from-ink-100 to-ink-200"></div>
        @endif
    </div>
    <div class="flex flex-1 flex-col p-4">
        <p class="text-[11px] uppercase tracking-wide text-ink-400">{{ $product->category?->name ?? 'Woodex' }}</p>
        <h3 class="mt-1 font-medium leading-snug text-ink-900 group-hover:text-brand-800">{{ $product->name }}</h3>
        @if ($product->short_description)
            <p class="mt-1 line-clamp-2 text-xs text-ink-500">{{ $product->short_description }}</p>
        @endif
        <div class="mt-auto flex items-center justify-between pt-3">
            <span class="text-sm font-semibold text-ink-900">
                {{ $product->is_quote_only ? 'On request' : money($product->price_minor, $product->currency) }}
            </span>
            @if ($product->compare_at_price_minor && $product->compare_at_price_minor > $product->price_minor)
                <span class="text-xs text-ink-400 line-through">{{ money($product->compare_at_price_minor, $product->currency) }}</span>
            @endif
        </div>
    </div>
</a>
