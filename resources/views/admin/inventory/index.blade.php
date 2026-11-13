<x-layouts.admin title="Inventory" heading="Inventory" subheading="Stock on hand, valued from the movement ledger."
                :breadcrumbs="['Operations' => route('admin.inventory.index')]">
    <div class="mb-4 grid gap-4 sm:grid-cols-3">
        <x-ui.stat label="SKUs" :value="$totals['skus']" />
        <x-ui.stat label="Units on hand" :value="number_format($totals['units'])" />
        <x-ui.stat label="Stock value at cost" :value="money($valuation)" :hint="$totals['low'].' SKUs at or below threshold'" />
    </div>

    <div class="grid gap-4 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="mb-3 flex items-center justify-between gap-3">
                <a href="{{ route('admin.inventory.index', ['low' => request('low') ? null : 1]) }}"
                   @class([
                       'rounded-full px-3 py-1.5 text-xs font-medium',
                       'bg-amber-600 text-white' => request('low'),
                       'bg-white text-ink-600 ring-1 ring-inset ring-ink-200 hover:bg-ink-50' => ! request('low'),
                   ])>Low stock only</a>
                <x-ui.search placeholder="Search name or SKU…" />
            </div>

            <x-ui.card :padding="false">
                <div class="px-5 py-1">
                    <x-ui.table :head="['Product', 'On hand', 'Threshold', 'Value', '']">
                        @foreach ($products as $product)
                            <tr class="{{ $product->isLowStock() ? 'bg-amber-50/50' : '' }}">
                                <td class="py-2.5 pl-0 pr-3">
                                    <a href="{{ route('admin.inventory.index', ['product' => $product->id]) }}"
                                       class="text-sm font-medium text-ink-900 hover:text-brand-700">{{ $product->name }}</a>
                                    <p class="font-mono text-[10px] text-ink-400">{{ $product->sku }}</p>
                                </td>
                                <td class="px-3 py-2.5 text-sm font-medium tabular-nums {{ $product->isLowStock() ? 'text-amber-800' : 'text-ink-800' }}">
                                    {{ $product->stock_quantity }}
                                </td>
                                <td class="px-3 py-2.5 text-xs tabular-nums text-ink-500">{{ $product->low_stock_threshold }}</td>
                                <td class="px-3 py-2.5 text-sm tabular-nums text-ink-700">{{ money($product->cost_minor * $product->stock_quantity) }}</td>
                                <td class="py-2.5 pl-3 pr-0 text-right">
                                    <a href="{{ route('admin.inventory.index', ['product' => $product->id]) }}"
                                       class="text-xs font-medium text-brand-700">Adjust →</a>
                                </td>
                            </tr>
                        @endforeach
                    </x-ui.table>
                </div>
            </x-ui.card>
            <x-ui.pagination :paginator="$products" />
        </div>

        <div class="space-y-4">
            @if ($selected)
                <x-ui.card :title="'Adjust '.$selected->name" :description="'Currently '.$selected->stock_quantity.' on hand'">
                    <form method="POST" action="{{ route('admin.inventory.adjust', $selected) }}" class="space-y-3">
                        @csrf
                        <x-ui.field label="Quantity" name="quantity" required hint="Use a negative number to remove stock.">
                            <input type="number" name="quantity" required value="1"
                                   class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                        </x-ui.field>
                        <x-ui.field label="Reason type" name="type">
                            <select name="type" class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                                @foreach (['adjustment' => 'Stock count adjustment', 'receipt' => 'Received', 'issue' => 'Issued', 'damage' => 'Damaged', 'return' => 'Customer return'] as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </x-ui.field>
                        <x-ui.field label="Note" name="reason" required>
                            <input type="text" name="reason" required placeholder="What happened?"
                                   class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                        </x-ui.field>
                        <x-ui.button type="submit" icon="check" class="w-full">Record movement</x-ui.button>
                    </form>
                </x-ui.card>
            @else
                <x-ui.card title="Adjust stock">
                    <p class="text-sm text-ink-500">Pick a product to record a receipt, issue or count correction. Every change is written to the ledger with the resulting balance.</p>
                </x-ui.card>
            @endif

            <x-ui.card title="Recent movements" :padding="false">
                <div class="max-h-80 overflow-y-auto px-4 py-2">
                    @forelse ($ledger as $movement)
                        <div class="flex items-center justify-between gap-2 border-b border-ink-100 py-2 last:border-0">
                            <div class="min-w-0">
                                <p class="truncate text-xs font-medium text-ink-800">{{ $movement->product?->name }}</p>
                                <p class="truncate text-[10px] text-ink-500">{{ $movement->reason }}</p>
                            </div>
                            <div class="shrink-0 text-right">
                                <p class="text-xs font-semibold tabular-nums {{ $movement->quantity < 0 ? 'text-red-600' : 'text-emerald-700' }}">
                                    {{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}
                                </p>
                                <p class="text-[10px] text-ink-400">→ {{ $movement->balance_after }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="py-4 text-center text-xs text-ink-500">No movements yet.</p>
                    @endforelse
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.admin>
