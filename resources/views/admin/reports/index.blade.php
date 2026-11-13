<x-layouts.admin title="Reports" heading="Reports" subheading="Sales, sources and stock at a glance."
                :breadcrumbs="['Growth' => route('admin.reports.index')]">
    <x-slot:actions>
        <form method="GET" class="flex items-end gap-2">
            <input type="date" name="from" value="{{ $from->format('Y-m-d') }}"
                   class="rounded-lg border-ink-200 py-1.5 text-xs focus:border-brand-400 focus:ring-brand-400">
            <input type="date" name="to" value="{{ $to->format('Y-m-d') }}"
                   class="rounded-lg border-ink-200 py-1.5 text-xs focus:border-brand-400 focus:ring-brand-400">
            <x-ui.button type="submit" variant="secondary">Apply</x-ui.button>
        </form>
    </x-slot:actions>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat label="Revenue" :value="money($summary['revenue_minor'])" :change="$summary['revenue_change']" />
        <x-ui.stat label="Orders" :value="$orderCount" :change="$summary['orders_change']" />
        <x-ui.stat label="Average order" :value="money($summary['average_order_minor'])" />
        <x-ui.stat label="Stock value" :value="money($stockValue)" hint="At cost" />
    </div>

    <div class="mt-4 grid gap-4 lg:grid-cols-3">
        <x-ui.card title="Revenue trend" class="lg:col-span-2">
            <x-admin.trend :series="$series" :height="140" />
        </x-ui.card>

        <x-ui.card title="Lead sources">
            @if ($sources->isEmpty())
                <p class="py-6 text-center text-sm text-ink-500">No leads in this period.</p>
            @else
                <ul class="space-y-2 text-sm">
                    @foreach ($sources as $source)
                        <li class="flex items-center justify-between">
                            <span class="text-ink-700">{{ \Illuminate\Support\Str::headline($source->source ?? 'Unknown') }}</span>
                            <span class="text-right">
                                <span class="block font-medium tabular-nums">{{ $source->total }}</span>
                                <span class="block text-[10px] text-ink-400">{{ money($source->value) }} est.</span>
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-ui.card>
    </div>

    <div class="mt-4 grid gap-4 lg:grid-cols-3">
        <x-ui.card title="Sales by category">
            @if ($salesByCategory->isEmpty())
                <p class="py-6 text-center text-sm text-ink-500">No product sales yet.</p>
            @else
                <x-admin.bar-list :items="$salesByCategory->mapWithKeys(fn ($row) => [
                        $row->category => ['name' => $row->category, 'total' => (int) $row->total],
                    ])" value-key="total" :format="fn ($v) => money($v)" />
            @endif
        </x-ui.card>

        <x-ui.card title="Best sellers">
            @if ($topProducts === [])
                <p class="py-6 text-center text-sm text-ink-500">No sales yet.</p>
            @else
                <x-admin.bar-list :items="collect($topProducts)->mapWithKeys(fn ($row) => [
                        $row['name'] => ['name' => $row['name'], 'quantity' => $row['quantity']],
                    ])" value-key="quantity" />
            @endif
        </x-ui.card>

        <x-ui.card title="Top customers" :padding="false">
            <div class="px-5 py-1">
                @forelse ($customers as $customer)
                    <div class="flex items-center justify-between gap-2 border-b border-ink-100 py-2.5 last:border-0">
                        <a href="{{ route('admin.customers.show', $customer) }}" class="text-sm text-ink-800 hover:text-brand-700">{{ $customer->name }}</a>
                        <span class="text-xs font-medium tabular-nums text-ink-900">{{ money($customer->lifetime_value_minor) }}</span>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-ink-500">No customers yet.</p>
                @endforelse
            </div>
        </x-ui.card>
    </div>
</x-layouts.admin>
