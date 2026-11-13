<x-layouts.admin title="Journal" heading="Journal" subheading="Articles published to the storefront blog."
                :breadcrumbs="['Website' => route('admin.posts.index')]">
    <x-slot:actions>
        <x-ui.button href="{{ route('admin.posts.create') }}" icon="plus">New article</x-ui.button>
    </x-slot:actions>

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap gap-1.5">
            @foreach (['' => 'All', 'published' => 'Published', 'draft' => 'Drafts', 'archived' => 'Archived'] as $value => $label)
                <a href="{{ route('admin.posts.index', array_filter(['status' => $value, 'q' => request('q')])) }}"
                   @class([
                       'rounded-full px-3 py-1.5 text-xs font-medium',
                       'bg-ink-900 text-white' => request('status', '') === $value,
                       'bg-white text-ink-600 ring-1 ring-inset ring-ink-200 hover:bg-ink-50' => request('status', '') !== $value,
                   ])>{{ $label }}</a>
            @endforeach
        </div>
        <x-ui.search placeholder="Search articles…" />
    </div>

    @if ($posts->isEmpty())
        <x-ui.empty title="No articles yet" description="Write about materials, projects or the workshop." icon="note">
            <x-slot:action><x-ui.button href="{{ route('admin.posts.create') }}" icon="plus">Write the first article</x-ui.button></x-slot:action>
        </x-ui.empty>
    @else
        <x-ui.card :padding="false">
            <div class="px-5 py-1">
                <x-ui.table :head="['Article', 'Category', 'Author', 'Published', 'Views', 'Status', '']">
                    @foreach ($posts as $post)
                        <tr>
                            <td class="py-3 pl-0 pr-3">
                                <a href="{{ route('admin.posts.edit', $post) }}" class="font-medium text-ink-900 hover:text-brand-700">{{ $post->title }}</a>
                                <p class="font-mono text-[10px] text-ink-400">/journal/{{ $post->slug }}</p>
                            </td>
                            <td class="px-3 py-3 text-xs text-ink-600">{{ $post->category?->name ?? '—' }}</td>
                            <td class="px-3 py-3 text-xs text-ink-600">{{ $post->author?->name ?? '—' }}</td>
                            <td class="px-3 py-3 text-xs text-ink-500">{{ $post->published_at?->format('j M Y') ?? '—' }}</td>
                            <td class="px-3 py-3 text-xs tabular-nums text-ink-600">{{ $post->views }}</td>
                            <td class="px-3 py-3"><x-ui.status :status="$post->status" /></td>
                            <td class="py-3 pl-3 pr-0 text-right">
                                <x-ui.button href="{{ route('admin.posts.edit', $post) }}" variant="ghost" icon="pencil">Edit</x-ui.button>
                            </td>
                        </tr>
                    @endforeach
                </x-ui.table>
            </div>
        </x-ui.card>
        <x-ui.pagination :paginator="$posts" />
    @endif
</x-layouts.admin>
