<x-layouts.admin title="Products" heading="Products" subheading="Every piece the studio sells or quotes."
                :breadcrumbs="['Commerce' => route('admin.products.index')]">
    <x-slot:actions>
        <x-ui.button href="{{ route('admin.products.create') }}" icon="plus">New product</x-ui.button>
    </x-slot:actions>

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap gap-1.5">
            @foreach (['' => 'All ('.$counts['all'].')', 'published' => 'Published ('.$counts['published'].')', 'draft' => 'Drafts ('.$counts['draft'].')', 'low' => 'Low stock ('.$counts['low'].')'] as $value => $label)
                @php $key = $value === 'low' ? 'stock' : 'status'; @endphp
                <a href="{{ route('admin.products.index', array_filter([$key => $value, 'q' => request('q')])) }}"
                   @class([
                       'rounded-full px-3 py-1.5 text-xs font-medium',
                       'bg-ink-900 text-white' => request($key, '') === $value,
                       'bg-white text-ink-600 ring-1 ring-inset ring-ink-200 hover:bg-ink-50' => request($key, '') !== $value,
                   ])>{{ $label }}</a>
            @endforeach
        </div>
        <x-ui.search placeholder="Search name or SKU…" />
    </div>

    @if ($products->isEmpty())
        <x-ui.empty title="No products match" description="Adjust the filters, or add a new piece." icon="box">
            <x-slot:action><x-ui.button href="{{ route('admin.products.create') }}" icon="plus">Add a product</x-ui.button></x-slot:action>
        </x-ui.empty>
    @else
        <x-ui.card :padding="false">
            <div class="px-5 py-1">
                <x-ui.table :head="['Product', 'SKU', 'Category', 'Price', 'Stock', 'Status', '']">
                    @foreach ($products as $product)
                        <tr>
                            <td class="py-3 pl-0 pr-3">
                                <div class="flex items-center gap-3">
                                    <div class="size-10 shrink-0 overflow-hidden rounded-lg bg-ink-100">
                                        @if ($product->featuredImage)
                                            <img src="{{ $product->featuredImage->url() }}" alt="" class="size-full object-cover">
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <a href="{{ route('admin.products.edit', $product) }}" class="block truncate font-medium text-ink-900 hover:text-brand-700">
                                            {{ $product->name }}
                                        </a>
                                        @if ($product->is_featured)
                                            <span class="text-[10px] font-medium uppercase tracking-wide text-brand-700">Featured</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-3 font-mono text-xs text-ink-500">{{ $product->sku }}</td>
                            <td class="px-3 py-3 text-xs text-ink-600">{{ $product->category?->name ?? '—' }}</td>
                            <td class="px-3 py-3 text-sm font-medium tabular-nums text-ink-900">
                                {{ $product->is_quote_only ? 'On request' : money($product->price_minor, $product->currency) }}
                            </td>
                            <td class="px-3 py-3">
                                @if ($product->track_stock)
                                    <span @class([
                                        'text-xs font-medium tabular-nums',
                                        'text-red-700' => $product->isLowStock() && $product->stock_quantity === 0,
                                        'text-amber-700' => $product->isLowStock() && $product->stock_quantity > 0,
                                        'text-ink-600' => ! $product->isLowStock(),
                                    ])>{{ $product->stock_quantity }} in stock</span>
                                @else
                                    <span class="text-xs text-ink-400">Not tracked</span>
                                @endif
                            </td>
                            <td class="px-3 py-3"><x-ui.status :status="$product->status" /></td>
                            <td class="py-3 pl-3 pr-0 text-right">
                                <x-ui.button href="{{ route('admin.products.edit', $product) }}" variant="ghost" icon="pencil">Edit</x-ui.button>
                            </td>
                        </tr>
                    @endforeach
                </x-ui.table>
            </div>
        </x-ui.card>

        <x-ui.pagination :paginator="$products" />
    @endif
</x-layouts.admin>
