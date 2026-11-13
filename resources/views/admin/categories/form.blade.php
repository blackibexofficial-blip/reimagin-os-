@php $isNew = ! $category->exists; @endphp
<x-layouts.admin :title="$isNew ? 'New category' : $category->name" :heading="$isNew ? 'New category' : $category->name"
                 :breadcrumbs="['Categories' => route('admin.categories.index')]">
    <form method="POST" action="{{ $isNew ? route('admin.categories.store') : route('admin.categories.update', $category) }}"
          class="grid gap-4 lg:grid-cols-3">
        @csrf
        @unless ($isNew) @method('PUT') @endunless

        <div class="space-y-4 lg:col-span-2">
            <x-ui.card title="Category">
                <div class="space-y-4">
                    <x-ui.field label="Name" name="name" required>
                        <input type="text" name="name" value="{{ old('name', $category->name) }}" required
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                    <x-ui.field label="Slug" name="slug" hint="Leave blank to derive it from the name.">
                        <input type="text" name="slug" value="{{ old('slug', $category->slug) }}"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                    <x-ui.field label="Description" name="description">
                        <textarea name="description" rows="3"
                                  class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">{{ old('description', $category->description) }}</textarea>
                    </x-ui.field>
                </div>
            </x-ui.card>

            <x-ui.card title="Search appearance">
                <div class="space-y-4">
                    <x-ui.field label="SEO title" name="seo_title">
                        <input type="text" name="seo_title" maxlength="70" value="{{ old('seo_title', $category->seo_title) }}"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                    <x-ui.field label="Meta description" name="seo_description">
                        <textarea name="seo_description" rows="2" maxlength="180"
                                  class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">{{ old('seo_description', $category->seo_description) }}</textarea>
                    </x-ui.field>
                </div>
            </x-ui.card>
        </div>

        <div class="space-y-4">
            <x-ui.card title="Placement">
                <div class="space-y-4">
                    <x-ui.field label="Parent category" name="parent_id">
                        <select name="parent_id" class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                            <option value="">Top level</option>
                            @foreach ($parents as $parent)
                                <option value="{{ $parent->id }}" @selected(old('parent_id', $category->parent_id) == $parent->id)>{{ $parent->name }}</option>
                            @endforeach
                        </select>
                    </x-ui.field>
                    <x-ui.field label="Sort order" name="position">
                        <input type="number" name="position" min="0" value="{{ old('position', $category->position) }}"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                    <label class="flex items-center gap-2.5 text-sm text-ink-800">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active))
                               class="size-4 rounded border-ink-300 text-brand-600 focus:ring-brand-500">
                        Show on the storefront
                    </label>
                </div>
            </x-ui.card>

            <div class="flex justify-end gap-2">
                <x-ui.button href="{{ route('admin.categories.index') }}" variant="secondary">Cancel</x-ui.button>
                <x-ui.button type="submit" icon="check">{{ $isNew ? 'Create' : 'Save' }}</x-ui.button>
            </div>

            @unless ($isNew)
                <x-ui.card title="Danger zone">
                    <x-ui.confirm-button :action="route('admin.categories.destroy', $category)" label="Delete category"
                                         message="Delete “{{ $category->name }}”?" />
                </x-ui.card>
            @endunless
        </div>
    </form>
</x-layouts.admin>
