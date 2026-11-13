<x-layouts.admin title="SEO" heading="SEO" subheading="Meta tags and search-readiness for every public address."
                :breadcrumbs="['Growth' => route('admin.seo.index')]">
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat label="Public records" :value="$summary['total']" hint="Pages, products and articles" />
        <x-ui.stat label="Complete" :value="$summary['healthy']" hint="Title + description of a good length" />
        <x-ui.stat label="Missing titles" :value="$summary['missing_title']" />
        <x-ui.stat label="Missing descriptions" :value="$summary['missing_description']" />
    </div>

    <div class="mt-4 grid gap-4 lg:grid-cols-3">
        <x-ui.card title="Audit" class="lg:col-span-2" :padding="false">
            <div class="px-5 py-1">
                <x-ui.table :head="['Record', 'Type', 'Title', 'Description', 'Issues']">
                    @foreach ($audit as $row)
                        <tr>
                            <td class="py-2.5 pl-0 pr-3">
                                <p class="text-sm font-medium text-ink-900">{{ $row['label'] }}</p>
                                <p class="font-mono text-[10px] text-ink-400">/{{ $row['slug'] }}</p>
                            </td>
                            <td class="px-3 py-2.5 text-xs text-ink-500">{{ $row['type'] }}</td>
                            <td class="px-3 py-2.5 text-xs tabular-nums {{ $row['has_title'] ? 'text-ink-600' : 'font-medium text-red-600' }}">
                                {{ $row['has_title'] ? $row['title_length'].' ch' : 'missing' }}
                            </td>
                            <td class="px-3 py-2.5 text-xs tabular-nums {{ $row['has_description'] ? 'text-ink-600' : 'font-medium text-red-600' }}">
                                {{ $row['has_description'] ? $row['description_length'].' ch' : 'missing' }}
                            </td>
                            <td class="px-3 py-2.5">
                                @if ($row['issues'] === [])
                                    <x-ui.badge tone="success">OK</x-ui.badge>
                                @else
                                    <span class="text-[11px] text-amber-700">{{ implode(', ', $row['issues']) }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </x-ui.table>
            </div>
        </x-ui.card>

        <x-ui.card title="Fix page meta" description="Update the tags for a CMS page.">
            <form method="POST" action="{{ route('admin.seo.update', $pages->first() ?? 0) }}" class="space-y-3"
                  x-data="{ page: '{{ $pages->first()?->id ?? '' }}' }" :action="'{{ url('admin/seo') }}/' + page">
                @csrf @method('PUT')
                <x-ui.field label="Page" name="page">
                    <select x-model="page" class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                        @foreach ($pages as $page)
                            <option value="{{ $page->id }}">{{ $page->title }}</option>
                        @endforeach
                    </select>
                </x-ui.field>
                <x-ui.field label="SEO title" name="seo_title">
                    <input type="text" name="seo_title" maxlength="70" value="{{ $pages->first()?->seo_title }}"
                           class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                </x-ui.field>
                <x-ui.field label="Meta description" name="seo_description">
                    <textarea name="seo_description" rows="3" maxlength="180"
                              class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">{{ $pages->first()?->seo_description }}</textarea>
                </x-ui.field>
                <x-ui.button type="submit" class="w-full" icon="check">Save meta tags</x-ui.button>
            </form>

            <div class="mt-4 border-t border-ink-100 pt-3 text-[11px] leading-relaxed text-ink-500">
                Titles should stay under 60 characters and descriptions between 60 and 160 — longer text is truncated by search engines.
            </div>
        </x-ui.card>
    </div>
</x-layouts.admin>
