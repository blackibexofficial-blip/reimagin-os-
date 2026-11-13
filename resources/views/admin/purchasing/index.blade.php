<x-layouts.admin title="Purchasing" heading="Purchasing" subheading="Supplier orders and goods received."
                :breadcrumbs="['Operations' => route('admin.purchasing.index')]">
    <div class="mb-4 grid gap-4 sm:grid-cols-3">
        <x-ui.stat label="Open commitments" :value="money($totals['open'])" hint="Draft, ordered and part-received" />
        <x-ui.stat label="Active suppliers" :value="$totals['suppliers']" />
        <x-ui.stat label="Completed orders" :value="$totals['received']" />
    </div>

    <div class="grid gap-4 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-2">
            @forelse ($orders as $order)
                <x-ui.card :title="$order->reference" :description="($order->supplier?->name ?? 'No supplier').' · expected '.($order->expected_at?->format('j M Y') ?? 'unscheduled')">
                    <x-slot:actions><x-ui.status :status="$order->status" /></x-slot:actions>

                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-[10px] uppercase tracking-wide text-ink-400">
                                <th class="pb-1 font-medium">Item</th>
                                <th class="pb-1 text-right font-medium">Ordered</th>
                                <th class="pb-1 text-right font-medium">Received</th>
                                <th class="pb-1 text-right font-medium">Cost</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink-100">
                            @foreach ($order->items as $item)
                                <tr>
                                    <td class="py-1.5">{{ $item->description }}</td>
                                    <td class="py-1.5 text-right tabular-nums">{{ $item->quantity }}</td>
                                    <td class="py-1.5 text-right tabular-nums {{ $item->received_quantity >= $item->quantity ? 'text-emerald-700' : 'text-ink-500' }}">
                                        {{ $item->received_quantity }}
                                    </td>
                                    <td class="py-1.5 text-right tabular-nums">{{ money($item->line_total_minor) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-3 flex items-center justify-between border-t border-ink-100 pt-2">
                        <p class="text-sm font-semibold">{{ money($order->total_minor) }}</p>
                        @if ($order->status !== 'received' && $order->items->isNotEmpty())
                            <form method="POST" action="{{ route('admin.purchasing.receive', $order) }}" class="flex flex-wrap items-end gap-2">
                                @csrf
                                @foreach ($order->items as $item)
                                    @if ($item->received_quantity < $item->quantity)
                                        <label class="text-[10px] text-ink-500">
                                            {{ \Illuminate\Support\Str::limit($item->description, 14) }}
                                            <input type="number" name="items[{{ $item->id }}]" min="0" max="{{ $item->quantity - $item->received_quantity }}"
                                                   value="{{ $item->quantity - $item->received_quantity }}"
                                                   class="mt-0.5 w-16 rounded-md border-ink-200 px-1.5 py-1 text-xs focus:border-brand-400 focus:ring-brand-400">
                                        </label>
                                    @endif
                                @endforeach
                                <x-ui.button type="submit" variant="secondary" icon="truck">Receive</x-ui.button>
                            </form>
                        @endif
                    </div>
                </x-ui.card>
            @empty
                <x-ui.empty title="No purchase orders" description="Raise one when you need materials or stock from a supplier." icon="truck" />
            @endforelse
            <x-ui.pagination :paginator="$orders" />
        </div>

        <x-ui.card title="New purchase order">
            <form method="POST" action="{{ route('admin.purchasing.store') }}" class="space-y-3"
                  x-data="{ items: [{ description: '', quantity: 1, unit_cost: 0, product_id: '' }] }">
                @csrf
                <x-ui.field label="Supplier" name="supplier_id" required>
                    <select name="supplier_id" required class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                        <option value="">Choose…</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </x-ui.field>
                <div class="grid grid-cols-2 gap-2">
                    <x-ui.field label="Expected" name="expected_at">
                        <input type="date" name="expected_at" class="mt-1.5 w-full rounded-lg border-ink-200 text-xs focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                    <x-ui.field label="Status" name="status">
                        <select name="status" class="mt-1.5 w-full rounded-lg border-ink-200 text-xs focus:border-brand-400 focus:ring-brand-400">
                            <option value="draft">Draft</option>
                            <option value="ordered">Ordered</option>
                        </select>
                    </x-ui.field>
                </div>

                <div class="space-y-2">
                    <template x-for="(item, index) in items" :key="index">
                        <div class="rounded-lg border border-ink-150 p-2">
                            <input type="text" :name="`items[${index}][description]`" x-model="item.description" required
                                   placeholder="Item description"
                                   class="w-full rounded-md border-ink-200 px-2 py-1 text-xs focus:border-brand-400 focus:ring-brand-400">
                            <div class="mt-1 grid grid-cols-2 gap-1">
                                <input type="number" min="1" :name="`items[${index}][quantity]`" x-model.number="item.quantity"
                                       placeholder="Qty" class="rounded-md border-ink-200 px-2 py-1 text-xs focus:border-brand-400 focus:ring-brand-400">
                                <input type="number" min="0" step="0.01" :name="`items[${index}][unit_cost]`" x-model.number="item.unit_cost"
                                       placeholder="Unit cost" class="rounded-md border-ink-200 px-2 py-1 text-xs focus:border-brand-400 focus:ring-brand-400">
                            </div>
                            <button type="button" @click="items.splice(index, 1)" x-show="items.length > 1"
                                    class="mt-1 text-[10px] font-medium text-red-600">Remove</button>
                        </div>
                    </template>
                    <button type="button" @click="items.push({ description: '', quantity: 1, unit_cost: 0, product_id: '' })"
                            class="w-full rounded-md border border-dashed border-ink-300 py-1 text-[11px] text-ink-600 hover:bg-ink-50">+ Add line</button>
                </div>

                <x-ui.field label="Notes" name="notes">
                    <textarea name="notes" rows="2" class="mt-1.5 w-full rounded-lg border-ink-200 text-xs focus:border-brand-400 focus:ring-brand-400"></textarea>
                </x-ui.field>

                <x-ui.button type="submit" icon="plus" class="w-full">Create order</x-ui.button>
            </form>
        </x-ui.card>
    </div>
</x-layouts.admin>
