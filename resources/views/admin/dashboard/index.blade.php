<x-layouts.admin title="Dashboard" heading="Studio overview" subheading="{{ now()->format('l, j F Y') }}">
    <x-slot:actions>
        <form method="GET" class="flex items-center gap-2">
            <select name="range" onchange="this.form.submit()"
                    class="rounded-lg border-ink-200 bg-white py-1.5 pl-2.5 pr-8 text-xs font-medium text-ink-700 focus:border-brand-400 focus:ring-brand-400">
                @foreach (['7d' => 'Last 7 days', '30d' => 'Last 30 days', '90d' => 'Last 90 days', 'month' => 'This month'] as $key => $label)
                    <option value="{{ $key }}" @selected($range === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </form>
        <x-ui.button href="{{ route('admin.pages.index') }}" variant="secondary" icon="file">Pages</x-ui.button>
        <x-ui.button href="{{ route('admin.orders.index') }}" icon="cart">Orders</x-ui.button>
    </x-slot:actions>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat label="Revenue" :value="money($summary['revenue_minor'])" :change="$summary['revenue_change']"
                   hint="{{ $summary['orders'] }} orders placed" />
        <x-ui.stat label="Collected" :value="money($summary['collected_minor'])"
                   hint="{{ money($summary['outstanding_minor']) }} still outstanding" />
        <x-ui.stat label="Open pipeline" :value="money($summary['quotation_value_minor'])"
                   :hint="$summary['open_quotations'].' quotations awaiting a decision'" />
        <x-ui.stat label="Conversion" :value="$summary['conversion_rate'].'%'"
                   :hint="$summary['open_leads'].' leads open · '.$summary['low_stock'].' low stock'" />
    </div>

    <div class="mt-4 grid gap-4 xl:grid-cols-3">
        <x-ui.card class="xl:col-span-2" title="Revenue" description="Confirmed orders by day">
            <div class="flex items-baseline justify-between">
                <p class="font-display text-2xl font-semibold text-ink-900">{{ money($summary['revenue_minor']) }}</p>
                <p class="text-xs text-ink-500">
                    {{ $summary['from']->format('j M') }} – {{ $summary['to']->format('j M Y') }}
                    · avg {{ money($summary['average_order_minor']) }}
                </p>
            </div>
            <x-admin.trend :series="$series" :height="120" class="mt-4" />
            <div class="mt-4 grid grid-cols-3 gap-3 border-t border-ink-100 pt-3 text-center">
                <div>
                    <p class="text-[11px] uppercase tracking-wide text-ink-500">Orders</p>
                    <p class="font-semibold text-ink-900">{{ $summary['orders'] }}</p>
                </div>
                <div>
                    <p class="text-[11px] uppercase tracking-wide text-ink-500">Appointments today</p>
                    <p class="font-semibold text-ink-900">{{ $today['appointments_today'] }}</p>
                </div>
                <div>
                    <p class="text-[11px] uppercase tracking-wide text-ink-500">Avg. order</p>
                    <p class="font-semibold text-ink-900">{{ money($summary['average_order_minor']) }}</p>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card title="Sales pipeline" description="Leads by stage" :padding="true">
            @php
                $totalLeads = collect($pipeline)->sum('count');
            @endphp
            @if ($totalLeads === 0)
                <p class="py-6 text-center text-sm text-ink-500">No leads captured yet.</p>
            @else
                <x-admin.bar-list :items="collect($pipeline)->map(fn ($row, $status) => [
                    'name' => \Illuminate\Support\Str::headline($status),
                    'value' => $row['count'],
                    'total' => $row['count'],
                ])->mapWithKeys(fn ($row, $status) => [\Illuminate\Support\Str::headline($status) => $row])"
                    value-key="value" />
                <div class="mt-4 flex items-center justify-between border-t border-ink-100 pt-3 text-xs">
                    <span class="text-ink-500">{{ $totalLeads }} leads open</span>
                    <a href="{{ route('admin.leads.index') }}" class="font-medium text-brand-700 hover:text-brand-800">Open pipeline →</a>
                </div>
            @endif
        </x-ui.card>
    </div>

    <div class="mt-4 grid gap-4 xl:grid-cols-3">
        <x-ui.card title="Recent orders" description="Latest work entering the workshop" :padding="false">
            <div class="px-5 py-2">
                @forelse ($recentOrders as $order)
                    <a href="{{ route('admin.orders.show', $order) }}"
                       class="flex items-center justify-between gap-3 border-b border-ink-100 py-2.5 last:border-0 hover:text-brand-700">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-ink-900">{{ $order->customer?->name ?? 'Guest' }}</p>
                            <p class="truncate text-xs text-ink-500">{{ $order->reference }} · {{ $order->placed_at?->format('j M') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold tabular-nums text-ink-900">{{ money($order->total_minor) }}</p>
                            <x-ui.status :status="$order->status" />
                        </div>
                    </a>
                @empty
                    <p class="py-6 text-center text-sm text-ink-500">No orders yet.</p>
                @endforelse
            </div>
        </x-ui.card>

        <x-ui.card title="Today" description="Showroom floor and desk" :padding="true">
            <div class="space-y-4">
                <div>
                    <p class="mb-2 text-[11px] font-semibold uppercase tracking-wide text-ink-500">Appointments</p>
                    @forelse ($appointments as $appointment)
                        <div class="flex items-baseline justify-between gap-2 py-1 text-sm">
                            <span class="truncate text-ink-800">{{ $appointment->name }}</span>
                            <span class="shrink-0 text-xs tabular-nums text-ink-500">{{ $appointment->starts_at->format('g:i A') }}</span>
                        </div>
                    @empty
                        <p class="text-xs text-ink-500">Nothing booked today.</p>
                    @endforelse
                </div>
                <div class="border-t border-ink-100 pt-3">
                    <p class="mb-2 text-[11px] font-semibold uppercase tracking-wide text-ink-500">Follow-ups due</p>
                    @forelse ($followUps as $lead)
                        <a href="{{ route('admin.leads.show', $lead) }}" class="flex items-baseline justify-between gap-2 py-1 text-sm hover:text-brand-700">
                            <span class="truncate">{{ $lead->name }}</span>
                            <span class="shrink-0 text-xs {{ $lead->next_follow_up_at->isPast() ? 'font-medium text-red-600' : 'text-ink-500' }}">
                                {{ $lead->next_follow_up_at->diffForHumans(short: true) }}
                            </span>
                        </a>
                    @empty
                        <p class="text-xs text-ink-500">No follow-ups queued.</p>
                    @endforelse
                </div>
            </div>
        </x-ui.card>

        <x-ui.card title="Needs attention" description="Stock and quotations" :padding="true">
            <div class="space-y-4">
                <div>
                    <p class="mb-2 text-[11px] font-semibold uppercase tracking-wide text-ink-500">Low stock</p>
                    @forelse ($lowStock as $product)
                        <div class="flex items-baseline justify-between gap-2 py-1 text-sm">
                            <span class="truncate text-ink-800">{{ $product->name }}</span>
                            <span class="shrink-0 text-xs font-medium text-amber-700">{{ $product->stock_quantity }} left</span>
                        </div>
                    @empty
                        <p class="text-xs text-ink-500">All stock levels healthy.</p>
                    @endforelse
                </div>
                <div class="border-t border-ink-100 pt-3">
                    <p class="mb-2 text-[11px] font-semibold uppercase tracking-wide text-ink-500">Largest open quotations</p>
                    @forelse ($openQuotations as $quotation)
                        <a href="{{ route('admin.quotations.show', $quotation) }}" class="flex items-baseline justify-between gap-2 py-1 text-sm hover:text-brand-700">
                            <span class="truncate">{{ $quotation->customer?->name ?? $quotation->reference }}</span>
                            <span class="shrink-0 text-xs tabular-nums text-ink-500">{{ money($quotation->total_minor) }}</span>
                        </a>
                    @empty
                        <p class="text-xs text-ink-500">No open quotations.</p>
                    @endforelse
                </div>
            </div>
        </x-ui.card>
    </div>

    <div class="mt-4 grid gap-4 xl:grid-cols-2">
        <x-ui.card title="My tasks" description="Assigned to you" :padding="true">
            @forelse ($myTasks as $task)
                <div class="flex items-start justify-between gap-3 border-b border-ink-100 py-2.5 last:border-0">
                    <div>
                        <p class="text-sm text-ink-900">{{ $task->title }}</p>
                        <p class="text-xs text-ink-500">
                            {{ $task->due_at ? $task->due_at->format('j M, g:i A') : 'No due date' }}
                        </p>
                    </div>
                    <x-ui.status :status="$task->status" />
                </div>
            @empty
                <p class="py-6 text-center text-sm text-ink-500">Nothing assigned. Enjoy the quiet.</p>
            @endforelse
        </x-ui.card>

        <x-ui.card title="Best sellers" description="Units across all confirmed orders" :padding="true">
            @if ($topProducts === [])
                <p class="py-6 text-center text-sm text-ink-500">No sales recorded yet.</p>
            @else
                <x-admin.bar-list :items="collect($topProducts)->mapWithKeys(fn ($row) => [
                        $row['name'] => ['name' => $row['name'], 'quantity' => $row['quantity'], 'revenue_minor' => $row['revenue_minor']],
                    ])"
                    value-key="quantity" />
            @endif
        </x-ui.card>
    </div>
</x-layouts.admin>
