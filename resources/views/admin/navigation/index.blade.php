<x-layouts.admin title="Navigation" heading="Navigation" subheading="The menus visitors see in the header and footer."
                :breadcrumbs="['Website' => route('admin.navigation.index')]">
    <div class="grid gap-4 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-2">
            @foreach ($locations as $location => $label)
                <x-ui.card :title="$label" :description="'Shown in the '.$location.' of every storefront page.'" :padding="false">
                    <div class="px-5 py-2">
                        @php $items = $items[$location] ?? collect(); @endphp
                        @forelse ($items as $item)
                            <div class="border-b border-ink-100 py-2.5 last:border-0">
                                <form method="POST" action="{{ route('admin.navigation.update', $item) }}" class="flex flex-wrap items-center gap-2">
                                    @csrf @method('PUT')
                                    <input type="text" name="label" value="{{ $item->label }}"
                                           class="w-36 rounded-md border-ink-200 px-2 py-1 text-sm focus:border-brand-400 focus:ring-brand-400">
                                    <input type="text" name="url" value="{{ $item->url }}" placeholder="/shop"
                                           class="w-40 rounded-md border-ink-200 px-2 py-1 text-xs focus:border-brand-400 focus:ring-brand-400">
                                    <input type="number" name="position" value="{{ $item->position }}" min="0"
                                           class="w-16 rounded-md border-ink-200 px-2 py-1 text-xs focus:border-brand-400 focus:ring-brand-400">
                                    <label class="flex items-center gap-1 text-[11px] text-ink-600">
                                        <input type="checkbox" name="is_active" value="1" @checked($item->is_active)
                                               class="size-3.5 rounded border-ink-300 text-brand-600"> Visible
                                    </label>
                                    <label class="flex items-center gap-1 text-[11px] text-ink-600">
                                        <input type="checkbox" name="open_in_new_tab" value="1" @checked($item->open_in_new_tab)
                                               class="size-3.5 rounded border-ink-300 text-brand-600"> New tab
                                    </label>
                                    <button type="submit" class="rounded-md border border-ink-200 px-2 py-1 text-[11px] font-semibold text-ink-700 hover:bg-ink-50">Save</button>
                                </form>

                                <div class="mt-1 flex items-center justify-between">
                                    <p class="text-[10px] text-ink-400">
                                        @if ($item->page_id)
                                            Links to page “{{ $item->page?->title }}”
                                        @else
                                            Links to {{ $item->url ?: 'nowhere yet' }}
                                        @endif
                                    </p>
                                    <form method="POST" action="{{ route('admin.navigation.destroy', $item) }}"
                                          x-data @submit.prevent="if (confirm('Remove this menu item?')) $el.submit()">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-[10px] font-medium text-red-600 hover:text-red-700">Remove</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <p class="py-6 text-center text-sm text-ink-500">Nothing in this menu yet.</p>
                        @endforelse
                    </div>
                </x-ui.card>
            @endforeach
        </div>

        <x-ui.card title="Add an item">
            <form method="POST" action="{{ route('admin.navigation.store') }}" class="space-y-3">
                @csrf
                <x-ui.field label="Menu" name="location">
                    <select name="location" class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                        @foreach ($locations as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </x-ui.field>
                <x-ui.field label="Label" name="label" required>
                    <input type="text" name="label" required class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                </x-ui.field>
                <x-ui.field label="CMS page" name="page_id" hint="Leave empty to use a custom URL.">
                    <select name="page_id" class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                        <option value="">Custom URL</option>
                        @foreach ($pages as $page)
                            <option value="{{ $page->id }}">{{ $page->title }}</option>
                        @endforeach
                    </select>
                </x-ui.field>
                <x-ui.field label="URL" name="url" hint="e.g. /shop or https://instagram.com/woodex">
                    <input type="text" name="url" class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                </x-ui.field>
                <x-ui.button type="submit" icon="plus" class="w-full">Add to menu</x-ui.button>
            </form>
        </x-ui.card>
    </div>
</x-layouts.admin>
