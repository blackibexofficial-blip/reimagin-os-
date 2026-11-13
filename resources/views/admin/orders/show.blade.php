<x-layouts.admin :title="$order->reference" :heading="$order->reference"
                 :subheading="$order->customer?->name ?? 'Guest order'"
                 :breadcrumbs="['Orders' => route('admin.orders.index')]">
    <x-slot:actions>
        <x-ui.status :status="$order->status" />
        <x-ui.status :status="$order->payment_status" />
    </x-slot:actions>

    <div class="grid gap-4 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-2">
            <x-ui.card title="Items" :padding="false">
                <div class="px-5 py-1">
                    <x-ui.table :head="['Item', 'SKU', 'Qty', 'Unit', 'Total']">
                        @foreach ($order->items as $item)
                            <tr>
                                <td class="py-3 pl-0 pr-3">
                                    <p class="text-sm text-ink-900">{{ $item->name }}</p>
                                    @if ($item->options)<p class="text-[11px] text-ink-500">{{ $item->options }}</p>@endif
                                </td>
                                <td class="px-3 py-3 font-mono text-xs text-ink-500">{{ $item->sku }}</td>
                                <td class="px-3 py-3 text-sm tabular-nums text-ink-700">{{ $item->quantity }}</td>
                                <td class="px-3 py-3 text-sm tabular-nums text-ink-600">{{ money($item->unit_price_minor) }}</td>
                                <td class="px-3 py-3 text-sm font-medium tabular-nums text-ink-900">{{ money($item->line_total_minor) }}</td>
                            </tr>
                        @endforeach
                    </x-ui.table>
                </div>
                <div class="space-y-1 border-t border-ink-150 bg-ink-50 px-5 py-3 text-sm">
                    <div class="flex justify-between"><span class="text-ink-600">Subtotal</span><span class="tabular-nums">{{ money($order->subtotal_minor) }}</span></div>
                    @if ($order->discount_minor)
                        <div class="flex justify-between text-emerald-700"><span>Discount</span><span class="tabular-nums">−{{ money($order->discount_minor) }}</span></div>
                    @endif
                    @if ($order->tax_minor)
                        <div class="flex justify-between"><span class="text-ink-600">Tax</span><span class="tabular-nums">{{ money($order->tax_minor) }}</span></div>
                    @endif
                    @if ($order->delivery_minor)
                        <div class="flex justify-between"><span class="text-ink-600">Delivery</span><span class="tabular-nums">{{ money($order->delivery_minor) }}</span></div>
                    @endif
                    <div class="flex justify-between border-t border-ink-200 pt-2 font-semibold"><span>Total</span><span class="tabular-nums">{{ money($order->total_minor) }}</span></div>
                    <div class="flex justify-between text-xs text-ink-500"><span>Paid</span><span class="tabular-nums">{{ money($order->paid_minor) }}</span></div>
                    <div class="flex justify-between text-xs font-medium {{ $order->balanceMinor() > 0 ? 'text-amber-700' : 'text-emerald-700' }}">
                        <span>Balance</span><span class="tabular-nums">{{ money($order->balanceMinor()) }}</span>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card title="Payments" :padding="false">
                <div class="px-5 py-1">
                    @if ($order->payments->isEmpty())
                        <p class="py-6 text-center text-sm text-ink-500">No payments recorded yet.</p>
                    @else
                        <x-ui.table :head="['Date', 'Reference', 'Method', 'Type', 'Amount', 'By']">
                            @foreach ($order->payments as $payment)
                                <tr>
                                    <td class="py-2.5 pl-0 pr-3 text-xs text-ink-500">{{ $payment->paid_at?->format('j M Y') }}</td>
                                    <td class="px-3 py-2.5 font-mono text-[11px] text-ink-600">{{ $payment->reference }}</td>
                                    <td class="px-3 py-2.5 text-xs text-ink-600">{{ \Illuminate\Support\Str::headline($payment->method) }}</td>
                                    <td class="px-3 py-2.5 text-xs text-ink-600">{{ \Illuminate\Support\Str::headline($payment->type) }}</td>
                                    <td class="px-3 py-2.5 text-sm font-medium tabular-nums {{ $payment->type === 'refund' ? 'text-red-600' : 'text-ink-900' }}">
                                        {{ $payment->type === 'refund' ? '−' : '' }}{{ money($payment->amount_minor) }}
                                    </td>
                                    <td class="px-3 py-2.5 text-xs text-ink-500">{{ $payment->recorder?->name ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </x-ui.table>
                    @endif
                </div>
            </x-ui.card>
        </div>

        <div class="space-y-4">
            <x-ui.card title="Fulfilment">
                <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="space-y-3">
                    @csrf @method('PUT')
                    <x-ui.field label="Status" name="status" required>
                        <select name="status" class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                            @foreach (\App\Models\Order::STATUSES as $status)
                                <option value="{{ $status }}" @selected($order->status === $status)>{{ \Illuminate\Support\Str::headline($status) }}</option>
                            @endforeach
                        </select>
                    </x-ui.field>
                    <x-ui.field label="Internal notes" name="notes">
                        <textarea name="notes" rows="3" class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">{{ $order->notes }}</textarea>
                    </x-ui.field>
                    <x-ui.button type="submit" icon="check">Update order</x-ui.button>
                </form>
            </x-ui.card>

            <x-ui.card title="Record a payment">
                <form method="POST" action="{{ route('admin.orders.payments.store', $order) }}" class="space-y-3">
                    @csrf
                    <x-ui.field label="Amount" name="amount" required>
                        <input type="number" name="amount" step="0.01" min="0.01" required
                               value="{{ number_format($order->balanceMinor() / 100, 2, '.', '') }}"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                    <div class="grid grid-cols-2 gap-3">
                        <x-ui.field label="Method" name="method">
                            <select name="method" class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                                @foreach (['cash' => 'Cash', 'bank_transfer' => 'Bank transfer', 'card' => 'Card', 'cheque' => 'Cheque', 'online' => 'Online'] as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </x-ui.field>
                        <x-ui.field label="Type" name="type">
                            <select name="type" class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                                @foreach (['deposit' => 'Deposit', 'balance' => 'Balance', 'installment' => 'Instalment', 'refund' => 'Refund'] as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </x-ui.field>
                    </div>
                    <x-ui.field label="Reference" name="reference" hint="Cheque number or transaction id.">
                        <input type="text" name="reference" class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                    <x-ui.button type="submit" variant="brand" icon="banknote">Record payment</x-ui.button>
                </form>
            </x-ui.card>

            <x-ui.card title="Customer">
                <div class="space-y-1 text-sm">
                    <p class="font-medium text-ink-900">{{ $order->customer?->name ?? 'Guest' }}</p>
                    @if ($order->customer?->email)<p class="text-ink-600">{{ $order->customer->email }}</p>@endif
                    @if ($order->phone)<p class="text-ink-600">{{ $order->phone }}</p>@endif
                    @if ($order->shipping_address)
                        <p class="mt-2 text-xs text-ink-500">{{ $order->shipping_address }}<br>{{ $order->city }}</p>
                    @endif
                    @if ($order->customer)
                        <a href="{{ route('admin.customers.show', $order->customer) }}" class="mt-2 inline-block text-xs font-medium text-brand-700 hover:text-brand-800">Open customer record →</a>
                    @endif
                </div>
            </x-ui.card>

            @if ($order->quotation)
                <x-ui.card title="Source">
                    <p class="text-sm text-ink-600">Converted from quotation</p>
                    <a href="{{ route('admin.quotations.show', $order->quotation) }}"
                       class="mt-1 inline-block font-mono text-sm font-medium text-brand-700 hover:text-brand-800">{{ $order->quotation->reference }}</a>
                </x-ui.card>
            @endif
        </div>
    </div>
</x-layouts.admin>
