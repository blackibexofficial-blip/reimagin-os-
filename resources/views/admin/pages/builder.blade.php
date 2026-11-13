<x-layouts.admin :title="$page->title.' — builder'" heading="Visual builder" :subheading="$page->title">
    <x-slot:actions>
        <a href="{{ route('admin.pages.preview', $page) }}" target="_blank" rel="noopener"
           class="inline-flex items-center gap-1.5 rounded-lg border border-ink-200 bg-white px-3 py-2 text-xs font-semibold text-ink-700 hover:bg-ink-50">
            <x-nav-icon name="eye" class="size-4" /> Preview
        </a>
        <x-ui.button href="{{ route('admin.pages.edit', $page) }}" variant="ghost" icon="cog">Settings</x-ui.button>
    </x-slot:actions>

    <div x-data="pageBuilder(@js($config))" x-cloak class="-mx-4 -my-6 sm:-mx-6 lg:-mx-8">
        {{-- Toolbar -------------------------------------------------------}}
        <div class="flex flex-wrap items-center gap-2 border-b border-ink-200 bg-white px-4 py-2.5 sm:px-6 lg:px-8">
            <div class="flex items-center gap-1">
                <button type="button" @click="undo()" :disabled="history.length === 0"
                        class="rounded-lg p-2 text-ink-500 hover:bg-ink-100 disabled:opacity-40" title="Undo">
                    <x-nav-icon name="arrow-path" class="size-4 -scale-x-100" />
                </button>
                <button type="button" @click="redo()" :disabled="future.length === 0"
                        class="rounded-lg p-2 text-ink-500 hover:bg-ink-100 disabled:opacity-40" title="Redo">
                    <x-nav-icon name="arrow-path" class="size-4" />
                </button>
            </div>

            <span class="h-5 w-px bg-ink-200"></span>

            <button type="button" @click="addPanelOpen = ! addPanelOpen"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-ink-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-ink-800">
                <x-nav-icon name="plus" class="size-4" /> Add block
            </button>
            <button type="button" @click="addBlock('section')"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-ink-200 px-3 py-1.5 text-xs font-medium text-ink-700 hover:bg-ink-50">
                <x-nav-icon name="layers" class="size-4" /> Section
            </button>

            <div class="ml-auto flex items-center gap-2">
                <span class="hidden text-[11px] text-ink-500 sm:inline" x-text="dirty ? 'Unsaved changes' : 'Saved ' + formatTime(lastSavedAt)"></span>
                <span x-show="notice" x-text="notice" class="text-[11px] font-medium text-emerald-700"></span>
                <span x-show="error" x-text="error" class="text-[11px] font-medium text-red-600"></span>
                <button type="button" @click="save()" :disabled="saving"
                        class="rounded-lg border border-ink-200 px-3 py-1.5 text-xs font-semibold text-ink-800 hover:bg-ink-50 disabled:opacity-50">
                    <span x-show="! saving">Save draft</span>
                    <span x-show="saving">Saving…</span>
                </button>
                <button type="button" @click="publish()" :disabled="publishing"
                        class="rounded-lg bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-700 disabled:opacity-50">
                    <span x-show="! publishing">Publish</span>
                    <span x-show="publishing">Publishing…</span>
                </button>
            </div>
        </div>

        <div class="flex bg-ink-100/70">
            {{-- Canvas ----------------------------------------------------}}
            <div class="min-w-0 flex-1">
                <div class="mx-auto max-w-3xl px-4 py-6">
                    <div class="overflow-hidden rounded-xl border border-ink-200 bg-white shadow-panel">
                        <div class="flex items-center gap-2 border-b border-ink-150 bg-ink-50 px-3 py-2">
                            <span class="flex gap-1">
                                <span class="size-2 rounded-full bg-ink-300"></span>
                                <span class="size-2 rounded-full bg-ink-300"></span>
                                <span class="size-2 rounded-full bg-ink-300"></span>
                            </span>
                            <span class="truncate rounded bg-white px-2 py-0.5 text-[11px] text-ink-500 ring-1 ring-inset ring-ink-200"
                                  x-text="'woodexstudio.pk/' + page.slug"></span>
                            <span class="ml-auto text-[10px] font-medium uppercase tracking-wide"
                                  :class="page.has_unpublished_changes ? 'text-amber-700' : 'text-emerald-700'"
                                  x-text="page.has_unpublished_changes ? 'Draft ahead of live' : 'Live'"></span>
                        </div>

                        <div class="min-h-[420px] bg-white">
                            <template x-if="blocks.length === 0">
                                <div class="grid place-items-center px-6 py-20 text-center">
                                    <div>
                                        <span class="mx-auto grid size-12 place-items-center rounded-full bg-ink-100 text-ink-400">
                                            <x-nav-icon name="layers" class="size-6" />
                                        </span>
                                        <p class="mt-4 text-sm font-semibold text-ink-800">The canvas is empty</p>
                                        <p class="mt-1 text-xs text-ink-500">Add a section, then drop blocks inside it.</p>
                                        <button type="button" @click="addPanelOpen = true"
                                                class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-ink-900 px-3 py-1.5 text-xs font-semibold text-white">
                                            <x-nav-icon name="plus" class="size-4" /> Add the first block
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <div class="builder-list" x-sort="$el.dispatch('blocks-reordered')">
                                <template x-for="block in blocks" :key="block.id">
                                    <div class="builder-block group"
                                         :data-selected="selectedId === block.id"
                                         @click="selectedId = block.id">
                                        <div class="absolute -top-3 left-2 z-10 hidden items-center gap-1 rounded-md bg-ink-900 px-1.5 py-1 text-[10px] text-white group-hover:flex"
                                             :class="selectedId === block.id ? 'flex' : 'hidden'">
                                            <span x-text="blockLabel(block)"></span>
                                            <button type="button" x-show="block.hidden" @click.stop="toggleHidden(block.id)" title="Show">
                                                <x-nav-icon name="eye" class="size-3" />
                                            </button>
                                            <button type="button" @click.stop="moveUp(block.id)" title="Move up">
                                                <x-nav-icon name="chevron-right" class="size-3 -rotate-90" />
                                            </button>
                                            <button type="button" @click.stop="moveDown(block.id)" title="Move down">
                                                <x-nav-icon name="chevron-right" class="size-3 rotate-90" />
                                            </button>
                                            <button type="button" @click.stop="duplicateBlock(block.id)" title="Duplicate">
                                                <x-nav-icon name="note" class="size-3" />
                                            </button>
                                            <button type="button" @click.stop="toggleHidden(block.id)" title="Hide">
                                                <x-nav-icon name="eye" class="size-3" />
                                            </button>
                                            <button type="button" @click.stop="removeBlock(block.id)" title="Delete">
                                                <x-nav-icon name="trash" class="size-3" />
                                            </button>
                                        </div>

                                        <div :class="block.hidden ? 'opacity-40' : ''" x-html="renderPreview(block)"></div>

                                        {{-- Nested container blocks --}}
                                        <template x-if="block.children && block.children.length">
                                            <div class="space-y-1 px-4 pb-4">
                                                <template x-for="child in block.children" :key="child.id">
                                                    <div class="builder-block rounded border border-ink-100"
                                                         :data-selected="selectedId === child.id"
                                                         @click.stop="selectedId = child.id">
                                                        <div class="absolute -top-2.5 left-2 z-10 rounded bg-brand-600 px-1.5 py-0.5 text-[9px] text-white"
                                                             x-text="blockLabel(child)"></div>
                                                        <div x-html="renderPreview(child)"></div>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>

                                        <button type="button" x-show="selectedId === block.id"
                                                @click.stop="addPanelOpen = true"
                                                class="absolute -bottom-3 left-1/2 z-10 -translate-x-1/2 rounded-full bg-brand-600 px-2 py-1 text-white shadow"
                                                title="Add a block after this one">
                                            <x-nav-icon name="plus" class="size-3" />
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Inspector -------------------------------------------------}}
            <aside class="hidden w-80 shrink-0 border-l border-ink-200 bg-white xl:block">
                <div class="border-b border-ink-150 px-4 py-3">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-ink-500">Inspector</p>
                    <p class="mt-0.5 truncate text-sm font-semibold text-ink-900"
                       x-text="selected ? blockLabel(selected) : 'Nothing selected'"></p>
                    <p class="mt-0.5 text-[11px] text-ink-500" x-text="selected ? blockDescription(selected) : 'Pick a block on the canvas.'"></p>
                </div>

                <div x-show="selected" class="max-h-[calc(100vh-16rem)] space-y-4 overflow-y-auto p-4">
                    <template x-for="[name, meta] in Object.entries(attrsFor(selected || { type: '' }))" :key="name">
                        <div>
                            <label class="block text-[11px] font-medium uppercase tracking-wide text-ink-500" x-text="meta.label"></label>

                            {{-- text / url --}}
                            <template x-if="meta.type === 'text' || meta.type === 'url'">
                                <input type="text" :value="selected.attrs[name]" @input="setAttr(name, $event.target.value)" @blur="endEdit()"
                                       class="mt-1 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                            </template>

                            {{-- textarea / code --}}
                            <template x-if="meta.type === 'textarea' || meta.type === 'code'">
                                <textarea rows="4" :value="selected.attrs[name]" @input="setAttr(name, $event.target.value)" @blur="endEdit()"
                                          class="mt-1 w-full rounded-lg border-ink-200 font-mono text-xs focus:border-brand-400 focus:ring-brand-400"></textarea>
                            </template>

                            {{-- select --}}
                            <template x-if="meta.type === 'select'">
                                <select :value="selected.attrs[name]" @change="setAttr(name, $event.target.value)"
                                        class="mt-1 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                                    <template x-for="[value, label] in Object.entries(meta.options || {})" :key="value">
                                        <option :value="value" x-text="label" :selected="String(selected.attrs[name]) === String(value)"></option>
                                    </template>
                                </select>
                            </template>

                            {{-- number --}}
                            <template x-if="meta.type === 'number'">
                                <input type="number" :min="meta.min ?? null" :max="meta.max ?? null"
                                       :value="selected.attrs[name]" @input="setAttr(name, Number($event.target.value))"
                                       class="mt-1 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                            </template>

                            {{-- boolean --}}
                            <template x-if="meta.type === 'boolean'">
                                <label class="mt-1.5 flex items-center gap-2 text-sm text-ink-700">
                                    <input type="checkbox" :checked="!! selected.attrs[name]" @change="setAttr(name, $event.target.checked)"
                                           class="size-4 rounded border-ink-300 text-brand-600 focus:ring-brand-500">
                                    <span x-text="meta.label"></span>
                                </label>
                            </template>

                            {{-- alignment --}}
                            <template x-if="meta.type === 'align'">
                                <div class="mt-1 flex gap-1">
                                    <template x-for="option in ['left', 'center', 'right']" :key="option">
                                        <button type="button" @click="setAttr(name, option)"
                                                class="flex-1 rounded-md border px-2 py-1 text-[11px] capitalize"
                                                :class="selected.attrs[name] === option ? 'border-brand-500 bg-brand-50 text-brand-800' : 'border-ink-200 text-ink-600 hover:bg-ink-50'"
                                                x-text="option"></button>
                                    </template>
                                </div>
                            </template>

                            {{-- media --}}
                            <template x-if="meta.type === 'media'">
                                <div class="mt-1">
                                    <div class="grid grid-cols-4 gap-1">
                                        <template x-for="asset in media.slice(0, 8)" :key="asset.id">
                                            <button type="button" @click="setAttr(name, asset.id)"
                                                    class="aspect-square overflow-hidden rounded border-2"
                                                    :class="String(selected.attrs[name]) === String(asset.id) ? 'border-brand-500' : 'border-transparent hover:border-ink-300'">
                                                <img :src="asset.thumb" :alt="asset.name" class="size-full object-cover">
                                            </button>
                                        </template>
                                    </div>
                                    <p class="mt-1 text-[10px] text-ink-400">Upload more in the media library.</p>
                                </div>
                            </template>

                            {{-- category --}}
                            <template x-if="meta.type === 'category'">
                                <select :value="selected.attrs[name]" @change="setAttr(name, $event.target.value)"
                                        class="mt-1 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                                    <option value="">All categories</option>
                                    @foreach (\App\Support\Blocks\BlockRegistry::categoryOptions() as $id => $label)
                                        <option value="{{ $id }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </template>

                            {{-- repeater (FAQ and similar) --}}
                            <template x-if="meta.type === 'repeater'">
                                <div class="mt-1 space-y-2">
                                    <template x-for="(row, index) in (selected.attrs[name] || [])" :key="index">
                                        <div class="rounded-lg border border-ink-150 p-2">
                                            <template x-for="[field, fieldLabel] in Object.entries(meta.fields || {})" :key="field">
                                                <input type="text" :placeholder="fieldLabel" :value="row[field]"
                                                       @input="row[field] = $event.target.value; markDirty()"
                                                       class="mb-1 w-full rounded-md border-ink-200 px-2 py-1 text-xs focus:border-brand-400 focus:ring-brand-400">
                                            </template>
                                            <button type="button"
                                                    @click="selected.attrs[name].splice(index, 1); markDirty()"
                                                    class="text-[10px] font-medium text-red-600">Remove</button>
                                        </div>
                                    </template>
                                    <button type="button"
                                            @click="(selected.attrs[name] = selected.attrs[name] || []).push({}); markDirty()"
                                            class="w-full rounded-md border border-dashed border-ink-300 py-1 text-[11px] text-ink-600 hover:bg-ink-50">
                                        Add row
                                    </button>
                                </div>
                            </template>

                            <p class="mt-1 text-[10px] text-ink-400" x-show="meta.type === 'code'">Unsafe tags are stripped on save.</p>
                        </div>
                    </template>
                </div>

                <div x-show="! selected" class="p-4 text-xs text-ink-500">
                    Select a block to edit its content, or drag blocks to reorder them.
                </div>
            </aside>
        </div>

        {{-- Add-block drawer ------------------------------------------------}}
        <div x-show="addPanelOpen" x-cloak class="fixed inset-0 z-50 flex justify-end">
            <div class="absolute inset-0 bg-ink-950/40" @click="addPanelOpen = false"></div>
            <div class="relative flex h-full w-full max-w-sm flex-col bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-ink-150 px-4 py-3">
                    <div>
                        <p class="font-display text-sm font-semibold text-ink-900">Add a block</p>
                        <p class="text-[11px] text-ink-500">Blocks are inserted after the current selection.</p>
                    </div>
                    <button type="button" @click="addPanelOpen = false" class="text-ink-400 hover:text-ink-700">
                        <x-nav-icon name="plus" class="size-4 rotate-45" />
                    </button>
                </div>

                <div class="flex-1 space-y-5 overflow-y-auto p-4">
                    @foreach (\App\Support\Blocks\BlockRegistry::library() as $group => $blocks)
                        <div>
                            <p class="mb-2 text-[10px] font-semibold uppercase tracking-[0.16em] text-ink-500">{{ $group }}</p>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach ($blocks as $type => $definition)
                                    <button type="button" @click="addBlock('{{ $type }}', selected && selected.children !== undefined ? selected.id : null)"
                                            class="rounded-lg border border-ink-200 p-2.5 text-left transition hover:border-brand-300 hover:bg-brand-50/40">
                                        <span class="flex items-center gap-1.5 text-xs font-semibold text-ink-800">
                                            <x-nav-icon :name="$definition['icon']" class="size-3.5 text-brand-600" />
                                            {{ $definition['label'] }}
                                        </span>
                                        <span class="mt-1 block text-[10px] leading-snug text-ink-500">{{ $definition['description'] }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
