<x-layouts.admin :title="$page->title.' — revisions'" heading="Revision history"
                 :subheading="'Every saved draft of '.$page->title"
                 :breadcrumbs="['Pages' => route('admin.pages.index')]">
    <x-slot:actions>
        <x-ui.button href="{{ route('admin.pages.builder', $page) }}" variant="secondary" icon="layers">Back to builder</x-ui.button>
    </x-slot:actions>

    <x-ui.card :padding="false">
        <div class="px-5 py-1">
            <x-ui.table :head="['Revision', 'Label', 'Saved by', 'When', '']">
                @foreach ($revisions as $revision)
                    <tr>
                        <td class="py-3 pl-0 pr-3 font-medium text-ink-900 tabular-nums">#{{ $revision->revision }}</td>
                        <td class="px-3 py-3 text-sm text-ink-700">{{ $revision->label ?? 'Draft' }}</td>
                        <td class="px-3 py-3 text-sm text-ink-600">{{ $revision->user?->name ?? 'System' }}</td>
                        <td class="px-3 py-3 text-xs text-ink-500">{{ $revision->created_at->format('j M Y, g:i A') }}</td>
                        <td class="py-3 pl-3 pr-0 text-right">
                            <form method="POST" action="{{ route('admin.pages.revisions.restore', [$page, $revision]) }}"
                                  x-data @submit.prevent="if (confirm('Restore revision #{{ $revision->revision }} into the draft?')) $el.submit()">
                                @csrf
                                <x-ui.button variant="secondary" type="submit" icon="arrow-path">Restore</x-ui.button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>
        </div>
    </x-ui.card>

    <x-ui.pagination :paginator="$revisions" />
</x-layouts.admin>
