<x-layouts.admin title="Production" heading="Production" subheading="The workshop board — orders by fulfilment stage."
                :breadcrumbs="['Operations' => route('admin.production.index')]">
    <div class="grid gap-3 lg:grid-cols-4">
        @foreach ($columns as $status => $orders)
            <div class="rounded-xl border border-ink-200 bg-ink-50/60">
                <div class="flex items-center justify-between border-b border-ink-200 px-3 py-2">
                    <div class="flex items-center gap-1.5">
                        <span @class([
                            'size-2 rounded-full',
                            'bg-ink-300' => $status === 'pending',
                            'bg-sky-400' => $status === 'confirmed',
                            'bg-amber-400' => $status === 'in_production',
                            'bg-emerald-400' => $status === 'ready',
                        ])></span>
                        <p class="text-xs font-semibold text-ink-800">{{ \Illuminate\Support\Str::headline($status) }}</p>
                    </div>
                    <span class="text-[11px] tabular-nums text-ink-500">{{ $orders->count() }}</span>
                </div>

                <div class="space-y-2 p-2">
                    @forelse ($orders as $order)
                        <div class="rounded-lg border border-ink-200 bg-white p-2.5 shadow-sm">
                            <div class="flex items-start justify-between gap-2">
                                <a href="{{ route('admin.orders.show', $order) }}" class="font-mono text-[11px] font-medium text-brand-700">{{ $order->reference }}</a>
                                <span class="text-[10px] text-ink-400">{{ $order->placed_at?->format('j M') }}</span>
                            </div>
                            <p class="mt-1 truncate text-xs font-medium text-ink-900">{{ $order->customer?->name ?? 'Guest' }}</p>
                            <p class="truncate text-[10px] text-ink-500">{{ $order->items()->count() }} items · {{ money($order->total_minor) }}</p>
                            <form method="POST" action="{{ route('admin.production.advance', $order) }}" class="mt-2">
                                @csrf
                                <button type="submit"
                                        class="w-full rounded-md bg-ink-900 py-1 text-[10px] font-semibold text-white hover:bg-ink-800">
                                    Move forward →
                                </button>
                            </form>
                        </div>
                    @empty
                        <p class="px-2 py-6 text-center text-[11px] text-ink-400">Nothing here</p>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-4 grid gap-4 lg:grid-cols-2">
        <x-ui.card title="Active projects" description="Stage plan for each live commission.">
            @forelse ($projects as $project)
                <div class="border-b border-ink-100 py-3 last:border-0">
                    <div class="flex items-center justify-between gap-2">
                        <div>
                            <a href="{{ route('admin.projects.edit', $project) }}" class="text-sm font-medium text-ink-900 hover:text-brand-700">{{ $project->name }}</a>
                            <p class="text-[11px] text-ink-500">{{ $project->customer?->name ?? 'Internal' }} · due {{ $project->due_date?->format('j M') ?? '—' }}</p>
                        </div>
                        <span class="text-xs font-semibold tabular-nums text-ink-700">{{ $project->progress() }}%</span>
                    </div>
                    <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-ink-100">
                        <div class="h-full rounded-full bg-brand-500" style="width: {{ max(2, $project->progress()) }}%"></div>
                    </div>
                    <div class="mt-2 flex flex-wrap gap-1">
                        @foreach ($project->stages as $stage)
                            <form method="POST" action="{{ route('admin.production.stage', $stage) }}">
                                @csrf
                                <input type="hidden" name="status" value="{{ $stage->status === 'done' ? 'in_progress' : 'done' }}">
                                <button type="submit" @class([
                                    'rounded-full px-2 py-0.5 text-[10px] font-medium ring-1 ring-inset transition',
                                    'bg-emerald-50 text-emerald-800 ring-emerald-200' => $stage->status === 'done',
                                    'bg-amber-50 text-amber-800 ring-amber-200' => $stage->status === 'in_progress',
                                    'bg-red-50 text-red-700 ring-red-200' => $stage->status === 'blocked',
                                    'bg-ink-50 text-ink-600 ring-ink-200' => ! in_array($stage->status, ['done', 'in_progress', 'blocked'], true),
                                ])>{{ $stage->name }}</button>
                            </form>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="py-6 text-center text-sm text-ink-500">No active projects.</p>
            @endforelse
        </x-ui.card>

        <x-ui.card title="Recently delivered" :padding="false">
            <div class="px-5 py-1">
                @forelse ($delivered as $order)
                    <div class="flex items-center justify-between gap-2 border-b border-ink-100 py-2.5 last:border-0">
                        <div>
                            <a href="{{ route('admin.orders.show', $order) }}" class="font-mono text-xs font-medium text-ink-900">{{ $order->reference }}</a>
                            <p class="text-[11px] text-ink-500">{{ $order->customer?->name }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-medium tabular-nums text-ink-900">{{ money($order->total_minor) }}</p>
                            <p class="text-[10px] text-ink-400">{{ $order->delivered_at?->format('j M Y') }}</p>
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-ink-500">Nothing delivered yet.</p>
                @endforelse
            </div>
        </x-ui.card>
    </div>
</x-layouts.admin>
