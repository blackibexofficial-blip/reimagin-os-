<x-layouts.admin title="Projects" heading="Projects" subheading="Live commissions and the public showcase."
                :breadcrumbs="['Website' => route('admin.projects.index')]">
    <x-slot:actions>
        <x-ui.button href="{{ route('admin.projects.create') }}" icon="plus">New project</x-ui.button>
    </x-slot:actions>

    <div class="grid gap-4 lg:grid-cols-2">
        <x-ui.card title="Live commissions" description="Work in progress with a customer and a budget." :padding="false">
            <div class="px-5 py-1">
                @if ($projects->isEmpty())
                    <p class="py-6 text-center text-sm text-ink-500">No projects yet.</p>
                @else
                    <x-ui.table :head="['Project', 'Customer', 'Due', 'Budget', 'Status']">
                        @foreach ($projects as $project)
                            <tr>
                                <td class="py-2.5 pl-0 pr-3">
                                    <a href="{{ route('admin.projects.edit', $project) }}" class="text-sm font-medium text-ink-900 hover:text-brand-700">{{ $project->name }}</a>
                                    <p class="font-mono text-[10px] text-ink-400">{{ $project->reference }}</p>
                                </td>
                                <td class="px-3 py-2.5 text-xs text-ink-600">{{ $project->customer?->name ?? '—' }}</td>
                                <td class="px-3 py-2.5 text-xs {{ $project->due_date?->isPast() ? 'font-medium text-red-600' : 'text-ink-500' }}">
                                    {{ $project->due_date?->format('j M') ?? '—' }}
                                </td>
                                <td class="px-3 py-2.5 text-sm tabular-nums">{{ money($project->budget_minor) }}</td>
                                <td class="px-3 py-2.5"><x-ui.status :status="$project->status" /></td>
                            </tr>
                        @endforeach
                    </x-ui.table>
                @endif
            </div>
            <div class="border-t border-ink-100 px-5 py-2">{{ $projects->links() }}</div>
        </x-ui.card>

        <x-ui.card title="Showcase" description="Published case studies on the storefront." :padding="false">
            <div class="px-5 py-1">
                @if ($showcase->isEmpty())
                    <p class="py-6 text-center text-sm text-ink-500">No showcase pieces yet.</p>
                @else
                    <x-ui.table :head="['Project', 'Location', 'Year', 'Status', '']">
                        @foreach ($showcase as $item)
                            <tr>
                                <td class="py-2.5 pl-0 pr-3">
                                    <p class="text-sm font-medium text-ink-900">{{ $item->title }}</p>
                                    <p class="text-[10px] text-ink-400">{{ $item->category }}</p>
                                </td>
                                <td class="px-3 py-2.5 text-xs text-ink-600">{{ $item->location }}</td>
                                <td class="px-3 py-2.5 text-xs text-ink-500">{{ $item->year }}</td>
                                <td class="px-3 py-2.5">
                                    <x-ui.badge :tone="$item->is_published ? 'success' : 'neutral'">{{ $item->is_published ? 'Live' : 'Draft' }}</x-ui.badge>
                                </td>
                                <td class="py-2.5 pl-3 pr-0 text-right">
                                    @if ($item->is_published)
                                        <a href="{{ route('site.projects.show', $item) }}" target="_blank" rel="noopener"
                                           class="text-xs font-medium text-brand-700">View →</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </x-ui.table>
                @endif
            </div>
            <div class="border-t border-ink-100 px-5 py-2">{{ $showcase->links() }}</div>
        </x-ui.card>
    </div>
</x-layouts.admin>
