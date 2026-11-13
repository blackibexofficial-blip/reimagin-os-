@php $isNew = ! $product->exists; @endphp
<x-layouts.admin :title="$isNew ? 'New product' : $product->name" :heading="$isNew ? 'New product' : $product->name"
                 subheading="Pricing, stock and the details shown on the storefront."
                 :breadcrumbs="['Products' => route('admin.products.index')]">
    <form method="POST" action="{{ $isNew ? route('admin.products.store') : route('admin.products.update', $product) }}"
          class="grid gap-4 lg:grid-cols-3">
        @csrf
        @unless ($isNew) @method('PUT') @endunless

        <div class="space-y-4 lg:col-span-2">
            <x-ui.card title="Essentials">
                <div class="space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-ui.field label="Name" name="name" required>
                            <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                                   class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                        </x-ui.field>
                        <x-ui.field label="SKU" name="sku" required>
                            <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" required
                                   class="mt-1.5 w-full rounded-lg border-ink-200 font-mono text-sm focus:border-brand-400 focus:ring-brand-400">
                        </x-ui.field>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-ui.field label="Category" name="category_id">
                            <select name="category_id" class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                                <option value="">Uncategorised</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </x-ui.field>
                        <x-ui.field label="Slug" name="slug" hint="Leave blank to derive it from the name.">
                            <input type="text" name="slug" value="{{ old('slug', $product->slug) }}"
                                   class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                        </x-ui.field>
                    </div>

                    <x-ui.field label="Short description" name="short_description" hint="One or two lines for cards and listings.">
                        <textarea name="short_description" rows="2"
                                  class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">{{ old('short_description', $product->short_description) }}</textarea>
                    </x-ui.field>

                    <x-ui.field label="Full description" name="description">
                        <textarea name="description" rows="6"
                                  class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">{{ old('description', $product->description) }}</textarea>
                    </x-ui.field>
                </div>
            </x-ui.card>

            <x-ui.card title="Pricing" description="Amounts are entered in rupees; the database stores minor units.">
                <div class="grid gap-4 sm:grid-cols-3">
                    <x-ui.field label="Price" name="price">
                        <input type="number" name="price" step="0.01" min="0"
                               value="{{ old('price', $product->price_minor ? number_format($product->price_minor / 100, 2, '.', '') : '') }}"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                    <x-ui.field label="Compare at" name="compare_at_price" hint="Strike-through price.">
                        <input type="number" name="compare_at_price" step="0.01" min="0"
                               value="{{ old('compare_at_price', $product->compare_at_price_minor ? number_format($product->compare_at_price_minor / 100, 2, '.', '') : '') }}"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                    <x-ui.field label="Cost" name="cost" hint="Used for stock valuation.">
                        <input type="number" name="cost" step="0.01" min="0"
                               value="{{ old('cost', $product->cost_minor ? number_format($product->cost_minor / 100, 2, '.', '') : '') }}"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                </div>

                <label class="mt-4 flex items-start gap-2.5 rounded-lg border border-ink-150 p-3">
                    <input type="checkbox" name="is_quote_only" value="1" @checked(old('is_quote_only', $product->is_quote_only))
                           class="mt-0.5 size-4 rounded border-ink-300 text-brand-600 focus:ring-brand-500">
                    <span class="text-sm">
                        <span class="font-medium text-ink-800">Quote only</span>
                        <span class="block text-xs text-ink-500">Hide the price and send visitors to the enquiry form.</span>
                    </span>
                </label>
            </x-ui.card>

            <x-ui.card title="Specification">
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.field label="Material" name="material">
                        <input type="text" name="material" value="{{ old('material', $product->material) }}"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                    <x-ui.field label="Finish" name="finish">
                        <input type="text" name="finish" value="{{ old('finish', $product->finish) }}"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                    <x-ui.field label="Dimensions" name="dimensions">
                        <input type="text" name="dimensions" value="{{ old('dimensions', $product->dimensions) }}" placeholder="240 × 60 × 220 cm"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                    <x-ui.field label="Lead time (days)" name="lead_time_days">
                        <input type="number" name="lead_time_days" min="0" max="365" value="{{ old('lead_time_days', $product->lead_time_days) }}"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                </div>
            </x-ui.card>

            <x-ui.card title="Search appearance">
                <div class="space-y-4">
                    <x-ui.field label="SEO title" name="seo_title">
                        <input type="text" name="seo_title" maxlength="70" value="{{ old('seo_title', $product->seo_title) }}"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                    <x-ui.field label="Meta description" name="seo_description">
                        <textarea name="seo_description" rows="2" maxlength="180"
                                  class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">{{ old('seo_description', $product->seo_description) }}</textarea>
                    </x-ui.field>
                </div>
            </x-ui.card>
        </div>

        <div class="space-y-4">
            <x-ui.card title="Visibility">
                <div class="space-y-4">
                    <x-ui.field label="Status" name="status" required>
                        <select name="status" class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                            @foreach (['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $product->status) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </x-ui.field>

                    @foreach (['is_active' => 'Active', 'is_featured' => 'Featured on the homepage', 'track_stock' => 'Track stock'] as $field => $label)
                        <label class="flex items-center gap-2.5 text-sm text-ink-800">
                            <input type="checkbox" name="{{ $field }}" value="1" @checked(old($field, $product->{$field}))
                                   class="size-4 rounded border-ink-300 text-brand-600 focus:ring-brand-500">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </x-ui.card>

            <x-ui.card title="Stock">
                <div class="space-y-4">
                    <x-ui.field label="Quantity on hand" name="stock_quantity" hint="Use the inventory ledger for audited changes.">
                        <input type="number" name="stock_quantity" min="0" value="{{ old('stock_quantity', $product->stock_quantity) }}"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                    <x-ui.field label="Low stock threshold" name="low_stock_threshold">
                        <input type="number" name="low_stock_threshold" min="0" value="{{ old('low_stock_threshold', $product->low_stock_threshold) }}"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                </div>
            </x-ui.card>

            <div class="flex justify-end gap-2">
                <x-ui.button href="{{ route('admin.products.index') }}" variant="secondary">Cancel</x-ui.button>
                <x-ui.button type="submit" icon="check">{{ $isNew ? 'Create product' : 'Save' }}</x-ui.button>
            </div>

            @unless ($isNew)
                <x-ui.card title="Danger zone">
                    <x-ui.confirm-button :action="route('admin.products.destroy', $product)" label="Delete product"
                                         message="Delete “{{ $product->name }}”? This cannot be undone." />
                </x-ui.card>
            @endunless
        </div>
    </form>
</x-layouts.admin>
