<x-layouts.admin :title="$customer->name" :heading="$customer->name"
                 :subheading="$customer->company ?: $customer->email"
                 :breadcrumbs="['Customers' => route('admin.customers.index')]">
    <x-slot:actions>
        <x-ui.button href="{{ route('admin.quotations.create', ['customer' => $customer->id]) }}" icon="document">New quotation</x-ui.button>
        <x-ui.button href="{{ route('admin.customers.edit', $customer) }}" variant="secondary" icon="pencil">Edit</x-ui.button>
    </x-slot:actions>

    <div class="grid gap-4 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-2">
            <x-ui.card title="Orders" :padding="false">
                <div class="px-5 py-1">
                    @if ($customer->orders->isEmpty())
                        <p class="py-6 text-center text-sm text-ink-500">No orders yet.</p>
                    @else
                        <x-ui.table :head="['Reference', 'Placed', 'Items', 'Total', 'Status']">
                            @foreach ($customer->orders as $order)
                                <tr>
                                    <td class="py-2.5 pl-0 pr-3">
                                        <a href="{{ route('admin.orders.show', $order) }}" class="font-mono text-xs font-medium text-brand-700">{{ $order->reference }}</a>
                                    </td>
                                    <td class="px-3 py-2.5 text-xs text-ink-500">{{ $order->placed_at?->format('j M Y') }}</td>
                                    <td class="px-3 py-2.5 text-xs text-ink-600">{{ $order->items->count() }}</td>
                                    <td class="px-3 py-2.5 text-sm tabular-nums">{{ money($order->total_minor) }}</td>
                                    <td class="px-3 py-2.5"><x-ui.status :status="$order->status" /></td>
                                </tr>
                            @endforeach
                        </x-ui.table>
                    @endif
                </div>
            </x-ui.card>

            @if ($customer->quotations->isNotEmpty())
                <x-ui.card title="Quotations" :padding="false">
                    <div class="px-5 py-1">
                        <x-ui.table :head="['Reference', 'Value', 'Valid until', 'Status']">
                            @foreach ($customer->quotations as $quotation)
                                <tr>
                                    <td class="py-2.5 pl-0 pr-3">
                                        <a href="{{ route('admin.quotations.show', $quotation) }}" class="font-mono text-xs font-medium text-brand-700">{{ $quotation->reference }}</a>
                                    </td>
                                    <td class="px-3 py-2.5 text-sm tabular-nums">{{ money($quotation->total_minor) }}</td>
                                    <td class="px-3 py-2.5 text-xs text-ink-500">{{ $quotation->valid_until?->format('j M Y') }}</td>
                                    <td class="px-3 py-2.5"><x-ui.status :status="$quotation->status" /></td>
                                </tr>
                            @endforeach
                        </x-ui.table>
                    </div>
                </x-ui.card>
            @endif

            <x-ui.card title="Timeline">
                <ol class="space-y-3">
                    @forelse ($customer->activities as $activity)
                        <li class="flex gap-3">
                            <span class="mt-1 grid size-6 shrink-0 place-items-center rounded-full bg-ink-100 text-ink-500">
                                <x-nav-icon name="note" class="size-3" />
                            </span>
                            <div>
                                <p class="text-sm text-ink-800">{{ $activity->title }}</p>
                                @if ($activity->body)<p class="text-xs text-ink-500">{{ $activity->body }}</p>@endif
                                <p class="text-[11px] text-ink-400">{{ $activity->created_at->format('j M Y') }}</p>
                            </div>
                        </li>
                    @empty
                        <li class="py-4 text-center text-sm text-ink-500">Nothing logged yet.</li>
                    @endforelse
                </ol>
            </x-ui.card>
        </div>

        <div class="space-y-4">
            <x-ui.card title="Details">
                <dl class="space-y-2 text-sm">
                    <div><dt class="text-[11px] uppercase tracking-wide text-ink-400">Email</dt><dd>{{ $customer->email ?: '—' }}</dd></div>
                    <div><dt class="text-[11px] uppercase tracking-wide text-ink-400">Phone</dt><dd>{{ $customer->phone ?: '—' }}</dd></div>
                    <div><dt class="text-[11px] uppercase tracking-wide text-ink-400">Address</dt>
                        <dd class="text-ink-700">
                            {{ collect([$customer->address_line_1, $customer->address_line_2, $customer->city, $customer->postal_code])->filter()->implode(', ') ?: '—' }}
                        </dd>
                    </div>
                    <div><dt class="text-[11px] uppercase tracking-wide text-ink-400">Owner</dt><dd>{{ $customer->owner?->name ?? '—' }}</dd></div>
                    <div><dt class="text-[11px] uppercase tracking-wide text-ink-400">Lifetime value</dt><dd class="font-semibold">{{ money($customer->lifetime_value_minor) }}</dd></div>
                    <div><dt class="text-[11px] uppercase tracking-wide text-ink-400">Last order</dt><dd>{{ $customer->last_order_at?->format('j M Y') ?? '—' }}</dd></div>
                </dl>
                @if ($customer->tags)
                    <div class="mt-3 flex flex-wrap gap-1">
                        @foreach ($customer->tags as $tag)
                            <x-ui.badge tone="brand">{{ $tag }}</x-ui.badge>
                        @endforeach
                    </div>
                @endif
            </x-ui.card>

            @if ($customer->notes)
                <x-ui.card title="Notes">
                    <p class="whitespace-pre-line text-sm text-ink-700">{{ $customer->notes }}</p>
                </x-ui.card>
            @endif
        </div>
    </div>
</x-layouts.admin>
