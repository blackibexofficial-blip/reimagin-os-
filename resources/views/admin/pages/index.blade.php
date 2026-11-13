<x-layouts.admin title="Pages" heading="Pages" subheading="Every address on the public site, with its draft and published state."
                :breadcrumbs="['Website' => route('admin.pages.index')]">
    <x-slot:actions>
        <x-ui.button href="{{ route('admin.pages.create') }}" icon="plus">New page</x-ui.button>
    </x-slot:actions>

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-1.5">
            @foreach (['' => 'All ('.$counts['all'].')', 'published' => 'Published ('.$counts['published'].')', 'draft' => 'Drafts ('.$counts['draft'].')'] as $value => $label)
                <a href="{{ route('admin.pages.index', array_filter(['status' => $value, 'q' => request('q')])) }}"
                   @class([
                       'rounded-full px-3 py-1.5 text-xs font-medium transition',
                       'bg-ink-900 text-white' => request('status', '') === $value,
                       'bg-white text-ink-600 ring-1 ring-inset ring-ink-200 hover:bg-ink-50' => request('status', '') !== $value,
                   ])>{{ $label }}</a>
            @endforeach
        </div>
        <x-ui.search placeholder="Search pages…" />
    </div>

    @if ($pages->isEmpty())
        <x-ui.empty title="No pages yet" description="Start with the homepage, then add the pages your studio needs." icon="file">
            <x-slot:action>
                <x-ui.button href="{{ route('admin.pages.create') }}" icon="plus">Create the first page</x-ui.button>
            </x-slot:action>
        </x-ui.empty>
    @else
        <x-ui.card :padding="false">
            <div class="px-5 py-1">
                <x-ui.table :head="['Page', 'Address', 'Status', 'Blocks', 'Updated', '']">
                    @foreach ($pages as $page)
                        <tr class="align-middle">
                            <td class="py-3 pl-0 pr-3">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-ink-900">{{ $page->title }}</span>
                                    @if ($page->is_homepage)
                                        <x-ui.badge tone="brand">Homepage</x-ui.badge>
                                    @endif
                                    @if ($page->hasUnpublishedChanges())
                                        <x-ui.badge tone="warning">Unpublished edits</x-ui.badge>
                                    @endif
                                </div>
                                @if ($page->excerpt)
                                    <p class="mt-0.5 line-clamp-1 text-xs text-ink-500">{{ $page->excerpt }}</p>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-xs text-ink-500">/{{ $page->slug }}</td>
                            <td class="px-3 py-3"><x-ui.status :status="$page->status" /></td>
                            <td class="px-3 py-3 text-xs tabular-nums text-ink-600">{{ count($page->content ?? []) }}</td>
                            <td class="px-3 py-3 text-xs text-ink-500">
                                {{ $page->updated_at->diffForHumans(short: true) }}
                                <span class="block text-[11px] text-ink-400">{{ $page->author?->name ?? '—' }}</span>
                            </td>
                            <td class="py-3 pl-3 pr-0">
                                <div class="flex items-center justify-end gap-1">
                                    <x-ui.button href="{{ route('admin.pages.builder', $page) }}" variant="ghost" icon="pencil">Build</x-ui.button>
                                    @if ($page->isPublished())
                                        <a href="{{ $page->publicUrl() }}" target="_blank" rel="noopener"
                                           class="rounded-lg p-2 text-ink-400 hover:bg-ink-100 hover:text-ink-700" title="View live page">
                                            <x-nav-icon name="external" class="size-4" />
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-ui.table>
            </div>
        </x-ui.card>

        <x-ui.pagination :paginator="$pages" />
    @endif
</x-layouts.admin>
