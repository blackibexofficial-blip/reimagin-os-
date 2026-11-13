<x-layouts.admin title="Orders" heading="Orders" subheading="Confirmed work moving through the workshop."
                :breadcrumbs="['Commerce' => route('admin.orders.index')]">
    <div class="mb-4 grid gap-4 sm:grid-cols-3">
        <x-ui.stat label="Orders" :value="$totals['count']" />
        <x-ui.stat label="Open value" :value="money($totals['open'])" hint="Not yet delivered or cancelled" />
        <x-ui.stat label="Outstanding" :value="money($totals['outstanding'])" hint="Unpaid balance across all orders" />
    </div>

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap gap-1.5">
            @foreach (['' => 'All'] + array_combine(\App\Models\Order::STATUSES, array_map(fn ($s) => \Illuminate\Support\Str::headline($s), \App\Models\Order::STATUSES)) as $value => $label)
                <a href="{{ route('admin.orders.index', array_filter(['status' => $value, 'q' => request('q')])) }}"
                   @class([
                       'rounded-full px-3 py-1.5 text-xs font-medium',
                       'bg-ink-900 text-white' => request('status', '') === $value,
                       'bg-white text-ink-600 ring-1 ring-inset ring-ink-200 hover:bg-ink-50' => request('status', '') !== $value,
                   ])>{{ $label }}</a>
            @endforeach
        </div>
        <x-ui.search placeholder="Search reference or customer…" />
    </div>

    @if ($orders->isEmpty())
        <x-ui.empty title="No orders match" description="Orders arrive from the storefront checkout or from a converted quotation." icon="cart" />
    @else
        <x-ui.card :padding="false">
            <div class="px-5 py-1">
                <x-ui.table :head="['Reference', 'Customer', 'Placed', 'Total', 'Paid', 'Status', '']">
                    @foreach ($orders as $order)
                        <tr>
                            <td class="py-3 pl-0 pr-3">
                                <a href="{{ route('admin.orders.show', $order) }}" class="font-mono text-xs font-medium text-ink-900 hover:text-brand-700">{{ $order->reference }}</a>
                            </td>
                            <td class="px-3 py-3">
                                <p class="text-sm text-ink-800">{{ $order->customer?->name ?? 'Guest' }}</p>
                                <p class="text-[11px] text-ink-500">{{ $order->city }}</p>
                            </td>
                            <td class="px-3 py-3 text-xs text-ink-500">{{ $order->placed_at?->format('j M Y') }}</td>
                            <td class="px-3 py-3 text-sm font-medium tabular-nums text-ink-900">{{ money($order->total_minor, $order->currency) }}</td>
                            <td class="px-3 py-3">
                                <span class="text-xs tabular-nums text-ink-600">{{ money($order->paid_minor) }}</span>
                                <x-ui.status :status="$order->payment_status" class="ml-1" />
                            </td>
                            <td class="px-3 py-3"><x-ui.status :status="$order->status" /></td>
                            <td class="py-3 pl-3 pr-0 text-right">
                                <x-ui.button href="{{ route('admin.orders.show', $order) }}" variant="ghost" icon="eye">Open</x-ui.button>
                            </td>
                        </tr>
                    @endforeach
                </x-ui.table>
            </div>
        </x-ui.card>
        <x-ui.pagination :paginator="$orders" />
    @endif
</x-layouts.admin>
