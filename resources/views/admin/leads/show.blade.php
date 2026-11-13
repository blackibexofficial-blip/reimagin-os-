<x-layouts.admin :title="$lead->name" :heading="$lead->name"
                 :subheading="\Illuminate\Support\Str::headline($lead->source).' · score '.$lead->score"
                 :breadcrumbs="['Leads' => route('admin.leads.index')]">
    <x-slot:actions>
        <x-ui.status :status="$lead->status" />
        <x-ui.button href="{{ route('admin.leads.edit', $lead) }}" variant="secondary" icon="pencil">Edit</x-ui.button>
    </x-slot:actions>

    <div class="grid gap-4 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-2">
            <x-ui.card title="Enquiry">
                <dl class="grid gap-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-[11px] uppercase tracking-wide text-ink-400">Email</dt><dd class="text-ink-800">{{ $lead->email ?: '—' }}</dd></div>
                    <div><dt class="text-[11px] uppercase tracking-wide text-ink-400">Phone</dt><dd class="text-ink-800">{{ $lead->phone ?: '—' }}</dd></div>
                    <div><dt class="text-[11px] uppercase tracking-wide text-ink-400">Company</dt><dd class="text-ink-800">{{ $lead->company ?: '—' }}</dd></div>
                    <div><dt class="text-[11px] uppercase tracking-wide text-ink-400">Interest</dt><dd class="text-ink-800">{{ $lead->product?->name ?? '—' }}</dd></div>
                    <div><dt class="text-[11px] uppercase tracking-wide text-ink-400">Estimated value</dt><dd class="font-medium text-ink-900">{{ money($lead->estimated_value_minor) }}</dd></div>
                    <div><dt class="text-[11px] uppercase tracking-wide text-ink-400">Priority</dt><dd class="text-ink-800">{{ \Illuminate\Support\Str::headline($lead->priority) }}</dd></div>
                </dl>
                @if ($lead->message)
                    <div class="mt-4 rounded-lg bg-ink-50 p-3 text-sm text-ink-700">{{ $lead->message }}</div>
                @endif
            </x-ui.card>

            <x-ui.card title="Activity" description="Every call, note and status change.">
                <form method="POST" action="{{ route('admin.leads.note', $lead) }}" class="mb-4 space-y-2">
                    @csrf
                    <input type="text" name="title" required placeholder="What happened?"
                           class="w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    <textarea name="body" rows="2" placeholder="Details (optional)"
                              class="w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400"></textarea>
                    <div class="flex items-center gap-2">
                        <input type="datetime-local" name="next_follow_up_at"
                               class="rounded-lg border-ink-200 py-1.5 text-xs focus:border-brand-400 focus:ring-brand-400">
                        <x-ui.button type="submit" variant="secondary" icon="note">Log activity</x-ui.button>
                    </div>
                </form>

                <ol class="space-y-3 border-t border-ink-100 pt-4">
                    @forelse ($lead->activities as $activity)
                        <li class="flex gap-3">
                            <span class="mt-1 grid size-6 shrink-0 place-items-center rounded-full bg-ink-100 text-ink-500">
                                <x-nav-icon :name="$activity->type === 'call' ? 'phone' : 'note'" class="size-3" />
                            </span>
                            <div>
                                <p class="text-sm text-ink-800">{{ $activity->title }}</p>
                                @if ($activity->body)<p class="text-xs text-ink-500">{{ $activity->body }}</p>@endif
                                <p class="text-[11px] text-ink-400">
                                    {{ $activity->created_at->format('j M Y, g:i A') }}{{ $activity->user ? ' · '.$activity->user->name : '' }}
                                </p>
                            </div>
                        </li>
                    @empty
                        <li class="py-4 text-center text-sm text-ink-500">No activity yet.</li>
                    @endforelse
                </ol>
            </x-ui.card>

            @if ($lead->quotations->isNotEmpty())
                <x-ui.card title="Quotations" :padding="false">
                    <div class="px-5 py-1">
                        <x-ui.table :head="['Reference', 'Total', 'Status', '']">
                            @foreach ($lead->quotations as $quotation)
                                <tr>
                                    <td class="py-2.5 pl-0 pr-3 font-mono text-xs">{{ $quotation->reference }}</td>
                                    <td class="px-3 py-2.5 text-sm tabular-nums">{{ money($quotation->total_minor) }}</td>
                                    <td class="px-3 py-2.5"><x-ui.status :status="$quotation->status" /></td>
                                    <td class="py-2.5 pl-3 pr-0 text-right">
                                        <a href="{{ route('admin.quotations.show', $quotation) }}" class="text-xs font-medium text-brand-700">Open →</a>
                                    </td>
                                </tr>
                            @endforeach
                        </x-ui.table>
                    </div>
                </x-ui.card>
            @endif
        </div>

        <div class="space-y-4">
            <x-ui.card title="Move the lead">
                <form method="POST" action="{{ route('admin.leads.status', $lead) }}" class="space-y-3">
                    @csrf
                    <x-ui.field label="Status" name="status">
                        <select name="status" class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                            @foreach (['new', 'contacted', 'qualified', 'proposal', 'won', 'lost'] as $status)
                                <option value="{{ $status }}" @selected($lead->status === $status)>{{ \Illuminate\Support\Str::headline($status) }}</option>
                            @endforeach
                        </select>
                    </x-ui.field>
                    <x-ui.field label="Lost reason" name="lost_reason" hint="Required when marking lost.">
                        <input type="text" name="lost_reason" value="{{ $lead->lost_reason }}"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                    <x-ui.button type="submit" icon="check" class="w-full">Update status</x-ui.button>
                </form>
            </x-ui.card>

            <x-ui.card title="Owner">
                @if ($lead->assignee)
                    <p class="text-sm text-ink-800">{{ $lead->assignee->name }}</p>
                    <p class="text-xs text-ink-500">{{ $lead->assignee->job_title }}</p>
                @else
                    <p class="text-sm text-ink-500">Nobody owns this lead yet.</p>
                @endif
                <form method="POST" action="{{ route('admin.leads.assign', $lead) }}" class="mt-3 flex gap-2">
                    @csrf
                    <select name="assigned_to" class="flex-1 rounded-lg border-ink-200 py-1.5 text-xs focus:border-brand-400 focus:ring-brand-400">
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected($lead->assigned_to === $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                    <x-ui.button type="submit" variant="secondary">Assign</x-ui.button>
                </form>
            </x-ui.card>

            <x-ui.card title="Convert">
                @if ($lead->customer)
                    <p class="text-sm text-ink-600">Already a customer.</p>
                    <a href="{{ route('admin.customers.show', $lead->customer) }}" class="mt-1 inline-block text-sm font-medium text-brand-700">Open customer →</a>
                @else
                    <p class="text-xs text-ink-500">Creates a customer record from this enquiry, reusing an existing customer with the same email or phone.</p>
                    <form method="POST" action="{{ route('admin.leads.convert', $lead) }}" class="mt-3">
                        @csrf
                        <x-ui.button type="submit" variant="brand" icon="users" class="w-full">Convert to customer</x-ui.button>
                    </form>
                @endif
            </x-ui.card>

            @if ($lead->tasks->isNotEmpty())
                <x-ui.card title="Tasks">
                    @foreach ($lead->tasks as $task)
                        <div class="flex items-center justify-between gap-2 py-1 text-sm">
                            <span class="text-ink-700">{{ $task->title }}</span>
                            <x-ui.status :status="$task->status" />
                        </div>
                    @endforeach
                </x-ui.card>
            @endif
        </div>
    </div>
</x-layouts.admin>
