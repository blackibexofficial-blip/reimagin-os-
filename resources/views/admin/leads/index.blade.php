<x-layouts.admin title="Leads" heading="Leads" subheading="Every enquiry, scored and queued for a follow-up."
                :breadcrumbs="['Customers' => route('admin.leads.index')]">
    <x-slot:actions>
        <x-ui.button href="{{ route('admin.leads.create') }}" icon="plus">Add lead</x-ui.button>
    </x-slot:actions>

    <div class="mb-4 grid gap-4 sm:grid-cols-3">
        <x-ui.stat label="Open leads" :value="$summary['open']" />
        <x-ui.stat label="Open value" :value="money($summary['value'])" />
        <x-ui.stat label="Conversion rate" :value="$summary['conversion'].'%'" hint="Won against all closed leads" />
    </div>

    <div class="mb-4 grid gap-4 lg:grid-cols-3">
        <x-ui.card title="Pipeline" class="lg:col-span-1">
            @php $total = collect($pipeline)->sum('count'); @endphp
            @if ($total === 0)
                <p class="py-4 text-center text-sm text-ink-500">No leads yet.</p>
            @else
                <ul class="space-y-2">
                    @foreach ($pipeline as $status => $row)
                        <li class="flex items-center justify-between text-xs">
                            <span class="text-ink-700">{{ \Illuminate\Support\Str::headline($status) }}</span>
                            <span class="tabular-nums text-ink-500">{{ $row['count'] }} · {{ money($row['value_minor']) }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-ui.card>

        <div class="lg:col-span-2">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                <div class="flex flex-wrap gap-1.5">
                    @foreach (['' => 'All', 'new' => 'New', 'contacted' => 'Contacted', 'qualified' => 'Qualified', 'proposal' => 'Proposal', 'won' => 'Won', 'lost' => 'Lost'] as $value => $label)
                        <a href="{{ route('admin.leads.index', array_filter(['status' => $value, 'q' => request('q')])) }}"
                           @class([
                               'rounded-full px-3 py-1.5 text-xs font-medium',
                               'bg-ink-900 text-white' => request('status', '') === $value,
                               'bg-white text-ink-600 ring-1 ring-inset ring-ink-200 hover:bg-ink-50' => request('status', '') !== $value,
                           ])>{{ $label }}</a>
                    @endforeach
                </div>
                <x-ui.search placeholder="Search name, email or phone…" />
            </div>

            @if ($leads->isEmpty())
                <x-ui.empty title="No leads match" description="Enquiries from the website appear here automatically." icon="spark">
                    <x-slot:action><x-ui.button href="{{ route('admin.leads.create') }}" icon="plus">Add one manually</x-ui.button></x-slot:action>
                </x-ui.empty>
            @else
                <x-ui.card :padding="false">
                    <div class="px-5 py-1">
                        <x-ui.table :head="['Lead', 'Source', 'Value', 'Owner', 'Follow-up', 'Status', '']">
                            @foreach ($leads as $lead)
                                <tr>
                                    <td class="py-3 pl-0 pr-3">
                                        <a href="{{ route('admin.leads.show', $lead) }}" class="font-medium text-ink-900 hover:text-brand-700">{{ $lead->name }}</a>
                                        <p class="text-[11px] text-ink-500">{{ $lead->email ?: $lead->phone }}</p>
                                    </td>
                                    <td class="px-3 py-3 text-xs text-ink-600">{{ \Illuminate\Support\Str::headline($lead->source) }}</td>
                                    <td class="px-3 py-3 text-sm tabular-nums text-ink-800">{{ money($lead->estimated_value_minor) }}</td>
                                    <td class="px-3 py-3 text-xs text-ink-600">{{ $lead->assignee?->name ?? 'Unassigned' }}</td>
                                    <td class="px-3 py-3 text-xs {{ $lead->next_follow_up_at?->isPast() ? 'font-medium text-red-600' : 'text-ink-500' }}">
                                        {{ $lead->next_follow_up_at?->diffForHumans(short: true) ?? '—' }}
                                    </td>
                                    <td class="px-3 py-3"><x-ui.status :status="$lead->status" /></td>
                                    <td class="py-3 pl-3 pr-0 text-right">
                                        <x-ui.button href="{{ route('admin.leads.show', $lead) }}" variant="ghost" icon="eye">Open</x-ui.button>
                                    </td>
                                </tr>
                            @endforeach
                        </x-ui.table>
                    </div>
                </x-ui.card>
                <x-ui.pagination :paginator="$leads" />
            @endif
        </div>
    </div>
</x-layouts.admin>
