<x-layouts.admin title="Finance" heading="Finance" subheading="Cash in, cash owed and money committed to suppliers."
                :breadcrumbs="['Operations' => route('admin.finance.index')]">
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
        <x-ui.stat label="Collected" :value="money($summary['collected'])" :hint="$from->format('j M').' – '.$to->format('j M')" />
        <x-ui.stat label="Invoiced" :value="money($summary['invoiced'])" hint="Orders placed in the period" />
        <x-ui.stat label="Outstanding" :value="money($summary['outstanding'])" hint="Unpaid on all open orders" />
        <x-ui.stat label="Supplier commitments" :value="money($summary['payables'])" hint="Open purchase orders" />
    </div>

    <div class="mt-4 grid gap-4 lg:grid-cols-3">
        <x-ui.card title="Revenue by month" description="Last six months" class="lg:col-span-2">
            @if ($monthly->isEmpty())
                <p class="py-6 text-center text-sm text-ink-500">No orders yet.</p>
            @else
                <x-admin.bar-list :items="$monthly->mapWithKeys(fn ($total, $month) => [
                        \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M Y') => ['name' => \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M Y'), 'total' => $total],
                    ])"
                    value-key="total" :format="fn ($v) => money($v)" />
            @endif
        </x-ui.card>

        <x-ui.card title="Payment methods">
            @if ($byMethod->isEmpty())
                <p class="py-6 text-center text-sm text-ink-500">No payments in this period.</p>
            @else
                <ul class="space-y-2.5 text-sm">
                    @foreach ($byMethod as $row)
                        <li class="flex items-center justify-between gap-3">
                            <span class="text-ink-700">{{ \Illuminate\Support\Str::headline($row->method) }}</span>
                            <span class="text-right">
                                <span class="block font-medium tabular-nums text-ink-900">{{ money($row->total) }}</span>
                                <span class="block text-[10px] text-ink-400">{{ $row->payments }} payments</span>
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-ui.card>
    </div>

    <div class="mt-4 grid gap-4 lg:grid-cols-2">
        <x-ui.card title="Debtors" description="Largest outstanding balances" :padding="false">
            <div class="px-5 py-1">
                @forelse ($debtors as $order)
                    <div class="flex items-center justify-between gap-2 border-b border-ink-100 py-2.5 last:border-0">
                        <div>
                            <a href="{{ route('admin.orders.show', $order) }}" class="text-sm font-medium text-ink-900 hover:text-brand-700">{{ $order->customer?->name ?? 'Guest' }}</a>
                            <p class="font-mono text-[10px] text-ink-400">{{ $order->reference }} · {{ $order->placed_at?->format('j M Y') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold tabular-nums text-amber-700">{{ money($order->balanceMinor()) }}</p>
                            <p class="text-[10px] text-ink-400">of {{ money($order->total_minor) }}</p>
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-ink-500">Everything is paid up.</p>
                @endforelse
            </div>
        </x-ui.card>

        <x-ui.card title="Recent payments" :padding="false">
            <div class="px-5 py-1">
                @forelse ($recent as $payment)
                    <div class="flex items-center justify-between gap-2 border-b border-ink-100 py-2.5 last:border-0">
                        <div>
                            <p class="text-sm text-ink-800">{{ $payment->order?->customer?->name ?? 'Guest' }}</p>
                            <p class="font-mono text-[10px] text-ink-400">
                                {{ $payment->order?->reference }} · {{ \Illuminate\Support\Str::headline($payment->method) }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium tabular-nums {{ $payment->type === 'refund' ? 'text-red-600' : 'text-ink-900' }}">
                                {{ $payment->type === 'refund' ? '−' : '' }}{{ money($payment->amount_minor) }}
                            </p>
                            <p class="text-[10px] text-ink-400">{{ $payment->paid_at?->format('j M') }}</p>
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-ink-500">No payments recorded.</p>
                @endforelse
            </div>
        </x-ui.card>
    </div>
</x-layouts.admin>
