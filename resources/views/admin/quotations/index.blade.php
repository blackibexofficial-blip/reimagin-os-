<x-layouts.admin title="Quotations" heading="Quotations" subheading="Priced proposals, from draft to a confirmed order."
                :breadcrumbs="['Commerce' => route('admin.quotations.index')]">
    <x-slot:actions>
        <x-ui.button href="{{ route('admin.quotations.create') }}" icon="plus">New quotation</x-ui.button>
    </x-slot:actions>

    <div class="mb-4 grid gap-4 sm:grid-cols-3">
        <x-ui.stat label="Awaiting decision" :value="$summary['count']" hint="Draft or sent" />
        <x-ui.stat label="Open value" :value="money($summary['open'])" />
        <x-ui.stat label="Approved value" :value="money($summary['won'])" hint="Approved or converted" />
    </div>

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap gap-1.5">
            @foreach (['' => 'All'] + array_combine(\App\Models\Quotation::STATUSES, array_map(fn ($s) => \Illuminate\Support\Str::headline($s), \App\Models\Quotation::STATUSES)) as $value => $label)
                <a href="{{ route('admin.quotations.index', array_filter(['status' => $value, 'q' => request('q')])) }}"
                   @class([
                       'rounded-full px-3 py-1.5 text-xs font-medium',
                       'bg-ink-900 text-white' => request('status', '') === $value,
                       'bg-white text-ink-600 ring-1 ring-inset ring-ink-200 hover:bg-ink-50' => request('status', '') !== $value,
                   ])>{{ $label }}</a>
            @endforeach
        </div>
        <x-ui.search placeholder="Search reference or customer…" />
    </div>

    @if ($quotations->isEmpty())
        <x-ui.empty title="No quotations yet" description="Price a project from a lead or a customer record." icon="document">
            <x-slot:action><x-ui.button href="{{ route('admin.quotations.create') }}" icon="plus">New quotation</x-ui.button></x-slot:action>
        </x-ui.empty>
    @else
        <x-ui.card :padding="false">
            <div class="px-5 py-1">
                <x-ui.table :head="['Reference', 'Customer', 'Value', 'Valid until', 'Revision', 'Status', '']">
                    @foreach ($quotations as $quotation)
                        <tr>
                            <td class="py-3 pl-0 pr-3">
                                <a href="{{ route('admin.quotations.show', $quotation) }}" class="font-mono text-xs font-medium text-ink-900 hover:text-brand-700">{{ $quotation->reference }}</a>
                            </td>
                            <td class="px-3 py-3 text-sm text-ink-800">{{ $quotation->customer?->name ?? '—' }}</td>
                            <td class="px-3 py-3 text-sm font-medium tabular-nums text-ink-900">{{ money($quotation->total_minor, $quotation->currency) }}</td>
                            <td class="px-3 py-3 text-xs {{ $quotation->valid_until?->isPast() ? 'text-red-600' : 'text-ink-500' }}">
                                {{ $quotation->valid_until?->format('j M Y') ?? '—' }}
                            </td>
                            <td class="px-3 py-3 text-xs tabular-nums text-ink-500">v{{ $quotation->revision }}</td>
                            <td class="px-3 py-3"><x-ui.status :status="$quotation->status" /></td>
                            <td class="py-3 pl-3 pr-0 text-right">
                                <x-ui.button href="{{ route('admin.quotations.show', $quotation) }}" variant="ghost" icon="eye">Open</x-ui.button>
                            </td>
                        </tr>
                    @endforeach
                </x-ui.table>
            </div>
        </x-ui.card>
        <x-ui.pagination :paginator="$quotations" />
    @endif
</x-layouts.admin>
