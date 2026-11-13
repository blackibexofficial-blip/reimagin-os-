<x-layouts.admin :title="$quotation->reference" :heading="$quotation->reference"
                 :subheading="'Revision '.$quotation->revision.' · '.($quotation->customer?->name ?? 'No customer')"
                 :breadcrumbs="['Quotations' => route('admin.quotations.index')]">
    <x-slot:actions>
        <x-ui.status :status="$quotation->status" />
    </x-slot:actions>

    @if (session('error'))
        <p class="mb-4 rounded-lg bg-red-50 px-4 py-2.5 text-sm text-red-800">{{ session('error') }}</p>
    @endif

    <div class="grid gap-4 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-2">
            <x-ui.card title="Line items" :padding="false">
                <div class="px-5 py-1">
                    <x-ui.table :head="['Description', 'Qty', 'Unit', 'Total']">
                        @foreach ($quotation->items as $item)
                            <tr>
                                <td class="py-3 pl-0 pr-3">
                                    <p class="text-sm text-ink-900">{{ $item->description }}</p>
                                    @if ($item->customisation)
                                        <p class="text-[11px] text-ink-500">{{ $item->customisation }}</p>
                                    @endif
                                    @if ($item->product)
                                        <p class="font-mono text-[10px] text-ink-400">{{ $item->product->sku }}</p>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-sm tabular-nums text-ink-700">{{ $item->quantity }}</td>
                                <td class="px-3 py-3 text-sm tabular-nums text-ink-600">{{ money($item->unit_price_minor) }}</td>
                                <td class="px-3 py-3 text-sm font-medium tabular-nums text-ink-900">{{ money($item->line_total_minor) }}</td>
                            </tr>
                        @endforeach
                    </x-ui.table>
                </div>
                <div class="space-y-1 border-t border-ink-150 bg-ink-50 px-5 py-3 text-sm">
                    <div class="flex justify-between"><span class="text-ink-600">Subtotal</span><span class="tabular-nums">{{ money($quotation->subtotal_minor) }}</span></div>
                    @if ($quotation->discount_minor)
                        <div class="flex justify-between text-emerald-700"><span>Discount</span><span class="tabular-nums">−{{ money($quotation->discount_minor) }}</span></div>
                    @endif
                    @if ($quotation->tax_minor)
                        <div class="flex justify-between"><span class="text-ink-600">Tax</span><span class="tabular-nums">{{ money($quotation->tax_minor) }}</span></div>
                    @endif
                    @if ($quotation->delivery_minor)
                        <div class="flex justify-between"><span class="text-ink-600">Delivery</span><span class="tabular-nums">{{ money($quotation->delivery_minor) }}</span></div>
                    @endif
                    <div class="flex justify-between border-t border-ink-200 pt-2 font-semibold"><span>Total</span><span class="tabular-nums">{{ money($quotation->total_minor) }}</span></div>
                </div>
            </x-ui.card>

            @if ($quotation->notes || $quotation->terms)
                <x-ui.card title="Notes and terms">
                    @if ($quotation->notes)
                        <p class="whitespace-pre-line text-sm text-ink-700">{{ $quotation->notes }}</p>
                    @endif
                    @if ($quotation->terms)
                        <div class="mt-3 border-t border-ink-100 pt-3">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-500">Terms</p>
                            <p class="mt-1 whitespace-pre-line text-xs text-ink-600">{{ $quotation->terms }}</p>
                        </div>
                    @endif
                </x-ui.card>
            @endif

            <x-ui.card title="Revision history" :padding="false">
                <div class="px-5 py-2">
                    @forelse ($quotation->revisions as $revision)
                        <div class="flex items-start justify-between gap-3 border-b border-ink-100 py-2.5 last:border-0">
                            <div>
                                <p class="text-sm text-ink-800">v{{ $revision->revision }} · {{ $revision->note }}</p>
                                <p class="text-[11px] text-ink-500">{{ $revision->user?->name ?? 'System' }} · {{ $revision->created_at->format('j M Y, g:i A') }}</p>
                            </div>
                            <span class="text-xs tabular-nums text-ink-500">{{ money($revision->snapshot['total_minor'] ?? 0) }}</span>
                        </div>
                    @empty
                        <p class="py-6 text-center text-sm text-ink-500">No revisions recorded.</p>
                    @endforelse
                </div>
            </x-ui.card>
        </div>

        <div class="space-y-4">
            <x-ui.card title="Next step">
                <div class="space-y-2">
                    @if (in_array($quotation->status, ['draft'], true))
                        <form method="POST" action="{{ route('admin.quotations.send', $quotation) }}">
                            @csrf
                            <x-ui.button type="submit" class="w-full" icon="mail">Mark as sent</x-ui.button>
                        </form>
                    @endif

                    @if (in_array($quotation->status, ['draft', 'sent'], true))
                        <form method="POST" action="{{ route('admin.quotations.approve', $quotation) }}">
                            @csrf
                            <x-ui.button type="submit" class="w-full" variant="brand" icon="check">Customer approved</x-ui.button>
                        </form>
                        <form method="POST" action="{{ route('admin.quotations.reject', $quotation) }}" class="space-y-2">
                            @csrf
                            <input type="text" name="reason" placeholder="Reason (optional)"
                                   class="w-full rounded-lg border-ink-200 text-xs focus:border-brand-400 focus:ring-brand-400">
                            <x-ui.button type="submit" class="w-full" variant="secondary" icon="trash">Mark as rejected</x-ui.button>
                        </form>
                    @endif

                    @if ($quotation->status === 'approved')
                        <form method="POST" action="{{ route('admin.quotations.convert', $quotation) }}">
                            @csrf
                            <x-ui.button type="submit" class="w-full" variant="brand" icon="cart">Convert to order</x-ui.button>
                        </form>
                    @endif

                    @if ($quotation->order)
                        <a href="{{ route('admin.orders.show', $quotation->order) }}"
                           class="block rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-800">
                            Order {{ $quotation->order->reference }} →
                        </a>
                    @endif

                    @if (in_array($quotation->status, ['converted', 'rejected', 'expired'], true))
                        <p class="text-xs text-ink-500">This quotation is closed and cannot change state.</p>
                    @endif
                </div>
            </x-ui.card>

            <x-ui.card title="Customer">
                <div class="space-y-1 text-sm">
                    <p class="font-medium text-ink-900">{{ $quotation->customer?->name ?? '—' }}</p>
                    @if ($quotation->customer?->email)<p class="text-ink-600">{{ $quotation->customer->email }}</p>@endif
                    @if ($quotation->customer?->phone)<p class="text-ink-600">{{ $quotation->customer->phone }}</p>@endif
                    @if ($quotation->valid_until)
                        <p class="mt-2 text-xs {{ $quotation->valid_until->isPast() ? 'text-red-600' : 'text-ink-500' }}">
                            Valid until {{ $quotation->valid_until->format('j F Y') }}
                        </p>
                    @endif
                </div>
            </x-ui.card>

            @if ($quotation->lead)
                <x-ui.card title="From lead">
                    <a href="{{ route('admin.leads.show', $quotation->lead) }}" class="text-sm font-medium text-brand-700 hover:text-brand-800">
                        {{ $quotation->lead->name }} →
                    </a>
                </x-ui.card>
            @endif
        </div>
    </div>
</x-layouts.admin>
