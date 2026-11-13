@php $isNew = ! $service->exists; @endphp
<x-layouts.admin :title="$isNew ? 'New service' : $service->title" :heading="$isNew ? 'New service' : 'Edit service'"
                 :breadcrumbs="['Services' => route('admin.services.index')]">
    <form method="POST" action="{{ $isNew ? route('admin.services.store') : route('admin.services.update', $service) }}"
          class="grid gap-4 lg:grid-cols-3">
        @csrf
        @unless ($isNew) @method('PUT') @endunless

        <div class="space-y-4 lg:col-span-2">
            <x-ui.card title="Service">
                <div class="space-y-4">
                    <x-ui.field label="Title" name="title" required>
                        <input type="text" name="title" required value="{{ old('title', $service->title) }}"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                    <x-ui.field label="Slug" name="slug">
                        <input type="text" name="slug" value="{{ old('slug', $service->slug) }}"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                    <x-ui.field label="Excerpt" name="excerpt">
                        <textarea name="excerpt" rows="2"
                                  class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">{{ old('excerpt', $service->excerpt) }}</textarea>
                    </x-ui.field>
                    <x-ui.field label="Body" name="body" hint="HTML is allowed.">
                        <textarea name="body" rows="8"
                                  class="mt-1.5 w-full rounded-lg border-ink-200 font-mono text-xs focus:border-brand-400 focus:ring-brand-400">{{ old('body', $service->body) }}</textarea>
                    </x-ui.field>
                </div>
            </x-ui.card>

            <x-ui.card title="Search appearance">
                <div class="space-y-4">
                    <x-ui.field label="SEO title" name="seo_title">
                        <input type="text" name="seo_title" maxlength="70" value="{{ old('seo_title', $service->seo_title) }}"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                    <x-ui.field label="Meta description" name="seo_description">
                        <textarea name="seo_description" rows="2" maxlength="180"
                                  class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">{{ old('seo_description', $service->seo_description) }}</textarea>
                    </x-ui.field>
                </div>
            </x-ui.card>
        </div>

        <div class="space-y-4">
            <x-ui.card title="Display">
                <div class="space-y-4">
                    <x-ui.field label="Icon" name="icon" hint="One of the panel's icon names, e.g. wrench, box, layers.">
                        <input type="text" name="icon" value="{{ old('icon', $service->icon) }}"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                    <x-ui.field label="Sort order" name="position">
                        <input type="number" name="position" min="0" value="{{ old('position', $service->position) }}"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                    <label class="flex items-center gap-2.5 text-sm text-ink-800">
                        <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $service->is_published))
                               class="size-4 rounded border-ink-300 text-brand-600 focus:ring-brand-500">
                        Publish on the site
                    </label>
                </div>
            </x-ui.card>

            <div class="flex justify-end gap-2">
                <x-ui.button href="{{ route('admin.services.index') }}" variant="secondary">Cancel</x-ui.button>
                <x-ui.button type="submit" icon="check">Save</x-ui.button>
            </div>

            @unless ($isNew)
                <x-ui.card title="Danger zone">
                    <x-ui.confirm-button :action="route('admin.services.destroy', $service)" label="Delete service"
                                         message="Delete “{{ $service->title }}”?" />
                </x-ui.card>
            @endunless
        </div>
    </form>
</x-layouts.admin>
