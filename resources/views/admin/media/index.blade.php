<x-layouts.admin title="Media library" heading="Media library" subheading="Images and documents used across the site and products."
                :breadcrumbs="['Website' => route('admin.media.index')]">
    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <form method="GET" class="flex items-center gap-2">
            <x-ui.search placeholder="Search assets…" />
            <select name="type" onchange="this.form.submit()"
                    class="rounded-lg border-ink-200 py-2 pl-2 pr-8 text-xs focus:border-brand-400 focus:ring-brand-400">
                <option value="">All files</option>
                <option value="image" @selected(request('type') === 'image')>Images</option>
            </select>
        </form>

        <form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data"
              class="flex flex-wrap items-end gap-2">
            @csrf
            <div>
                <label for="file" class="block text-[11px] font-medium text-ink-600">Upload</label>
                <input id="file" type="file" name="file" required accept="image/*,application/pdf"
                       class="mt-1 w-56 rounded-lg border border-ink-200 bg-white px-2 py-1.5 text-xs file:mr-2 file:rounded file:border-0 file:bg-ink-900 file:px-2 file:py-1 file:text-[11px] file:text-white">
            </div>
            <div>
                <label for="alt_text" class="block text-[11px] font-medium text-ink-600">Alt text</label>
                <input id="alt_text" type="text" name="alt_text" placeholder="Describe the image"
                       class="mt-1 w-48 rounded-lg border-ink-200 py-1.5 text-xs focus:border-brand-400 focus:ring-brand-400">
            </div>
            <x-ui.button type="submit" icon="plus">Upload</x-ui.button>
        </form>
    </div>

    @error('file')
        <p class="mb-4 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-800">{{ $message }}</p>
    @enderror

    @if ($assets->isEmpty())
        <x-ui.empty title="No assets yet" description="Upload photography, drawings or brochures to use in pages and products." icon="image" />
    @else
        <div class="grid gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
            @foreach ($assets as $asset)
                <div class="group overflow-hidden rounded-xl border border-ink-200 bg-white shadow-panel" x-data="{ open: false }">
                    <div class="relative aspect-square bg-ink-100">
                        @if ($asset->isImage())
                            <img src="{{ $asset->url() }}" alt="{{ $asset->alt_text ?: $asset->name }}" class="size-full object-cover">
                        @else
                            <div class="grid size-full place-items-center text-ink-400">
                                <x-nav-icon name="file" class="size-8" />
                            </div>
                        @endif
                        <button type="button" @click="open = ! open"
                                class="absolute right-2 top-2 rounded-md bg-ink-950/70 p-1.5 text-white opacity-0 transition group-hover:opacity-100"
                                aria-label="Asset actions">
                            <x-nav-icon name="cog" class="size-3.5" />
                        </button>
                    </div>

                    <div class="p-3">
                        <p class="truncate text-xs font-medium text-ink-900">{{ $asset->name }}</p>
                        <p class="mt-0.5 text-[10px] text-ink-500">
                            {{ $asset->humanSize() }}{{ $asset->dimensions() ? ' · '.$asset->dimensions() : '' }}
                        </p>
                        @if ($asset->usage_count > 0)
                            <p class="mt-1 text-[10px] font-medium text-amber-700">Used {{ $asset->usage_count }}×</p>
                        @endif
                    </div>

                    <div x-show="open" x-cloak class="space-y-2 border-t border-ink-100 bg-ink-50 p-3">
                        <form method="POST" action="{{ route('admin.media.update', $asset) }}" class="space-y-1.5">
                            @csrf @method('PUT')
                            <input type="text" name="name" value="{{ $asset->name }}"
                                   class="w-full rounded-md border-ink-200 px-2 py-1 text-xs focus:border-brand-400 focus:ring-brand-400">
                            <input type="text" name="alt_text" value="{{ $asset->alt_text }}" placeholder="Alt text"
                                   class="w-full rounded-md border-ink-200 px-2 py-1 text-xs focus:border-brand-400 focus:ring-brand-400">
                            <button type="submit" class="w-full rounded-md bg-ink-900 py-1 text-[11px] font-semibold text-white">Save</button>
                        </form>
                        <form method="POST" action="{{ route('admin.media.destroy', $asset) }}"
                              x-data @submit.prevent="if (confirm('Delete this asset?')) $el.submit()">
                            @csrf @method('DELETE')
                            <button type="submit" @disabled($asset->usage_count > 0)
                                    class="w-full rounded-md border border-red-200 py-1 text-[11px] font-semibold text-red-700 disabled:cursor-not-allowed disabled:opacity-40">
                                {{ $asset->usage_count > 0 ? 'In use' : 'Delete' }}
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <x-ui.pagination :paginator="$assets" />
    @endif
</x-layouts.admin>
