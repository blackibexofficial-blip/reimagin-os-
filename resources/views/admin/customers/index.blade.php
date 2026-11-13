<x-layouts.admin title="Customers" heading="Customers" subheading="Everyone who has bought or been quoted."
                :breadcrumbs="['Customers' => route('admin.customers.index')]">
    <x-slot:actions>
        <x-ui.button href="{{ route('admin.customers.create') }}" icon="plus">Add customer</x-ui.button>
    </x-slot:actions>

    <div class="mb-4 grid gap-4 sm:grid-cols-3">
        <x-ui.stat label="Customers" :value="$summary['count']" />
        <x-ui.stat label="Lifetime value" :value="money($summary['value'])" />
        <x-ui.stat label="Repeat buyers" :value="$summary['repeat']" hint="More than one order" />
    </div>

    <div class="mb-4 flex justify-end"><x-ui.search placeholder="Search customers…" /></div>

    @if ($customers->isEmpty())
        <x-ui.empty title="No customers yet" description="They are created when a lead converts or an order is placed." icon="users">
            <x-slot:action><x-ui.button href="{{ route('admin.customers.create') }}" icon="plus">Add one manually</x-ui.button></x-slot:action>
        </x-ui.empty>
    @else
        <x-ui.card :padding="false">
            <div class="px-5 py-1">
                <x-ui.table :head="['Customer', 'Contact', 'City', 'Orders', 'Lifetime value', '']">
                    @foreach ($customers as $customer)
                        <tr>
                            <td class="py-3 pl-0 pr-3">
                                <a href="{{ route('admin.customers.show', $customer) }}" class="font-medium text-ink-900 hover:text-brand-700">{{ $customer->name }}</a>
                                @if ($customer->company)<p class="text-[11px] text-ink-500">{{ $customer->company }}</p>@endif
                            </td>
                            <td class="px-3 py-3 text-xs text-ink-600">
                                {{ $customer->email ?: '—' }}
                                @if ($customer->phone)<span class="block text-ink-500">{{ $customer->phone }}</span>@endif
                            </td>
                            <td class="px-3 py-3 text-xs text-ink-600">{{ $customer->city ?: '—' }}</td>
                            <td class="px-3 py-3 text-sm tabular-nums text-ink-700">{{ $customer->orders_count }}</td>
                            <td class="px-3 py-3 text-sm font-medium tabular-nums text-ink-900">{{ money($customer->lifetime_value_minor) }}</td>
                            <td class="py-3 pl-3 pr-0 text-right">
                                <x-ui.button href="{{ route('admin.customers.show', $customer) }}" variant="ghost" icon="eye">Open</x-ui.button>
                            </td>
                        </tr>
                    @endforeach
                </x-ui.table>
            </div>
        </x-ui.card>
        <x-ui.pagination :paginator="$customers" />
    @endif
</x-layouts.admin>
