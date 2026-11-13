@php
    $attrs = $block['attrs'] ?? [];
    $query = \App\Models\Product::query()->published()->with('featuredImage');
    if (! empty($attrs['category_id'])) {
        $query->where('category_id', (int) $attrs['category_id']);
    }
    if (! empty($attrs['only_featured'])) {
        $query->featured();
    }
    $products = $query->orderByDesc('is_featured')->limit((int) ($attrs['limit'] ?? 6))->get();
    $columns = (string) ($attrs['columns'] ?? '3');
    $grid = ['2' => 'sm:grid-cols-2', '3' => 'sm:grid-cols-2 lg:grid-cols-3', '4' => 'sm:grid-cols-2 lg:grid-cols-4'][$columns] ?? 'sm:grid-cols-2 lg:grid-cols-3';
@endphp
<section class="mx-auto max-w-6xl px-5 py-4" data-block="product_grid">
    <h2 class="font-display text-2xl font-semibold tracking-tight text-ink-900" data-bind="heading">{{ $attrs['heading'] ?? '' }}</h2>
    <div class="mt-6 grid gap-5 {{ $grid }}">
        @foreach ($products as $product)
            <x-site.product-card :product="$product" />
        @endforeach
    </div>
    @if ($products->isEmpty())
        <p class="mt-4 rounded-lg border border-dashed border-ink-200 p-6 text-center text-sm text-ink-400">
            No published products in this selection yet.
        </p>
    @endif
</section>
