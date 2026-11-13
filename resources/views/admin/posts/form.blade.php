@php $isNew = ! $post->exists; @endphp
<x-layouts.admin :title="$isNew ? 'New article' : $post->title" :heading="$isNew ? 'New article' : 'Edit article'"
                 :breadcrumbs="['Journal' => route('admin.posts.index')]">
    <form method="POST" action="{{ $isNew ? route('admin.posts.store') : route('admin.posts.update', $post) }}"
          class="grid gap-4 lg:grid-cols-3">
        @csrf
        @unless ($isNew) @method('PUT') @endunless

        <div class="space-y-4 lg:col-span-2">
            <x-ui.card title="Article">
                <div class="space-y-4">
                    <x-ui.field label="Title" name="title" required>
                        <input type="text" name="title" required value="{{ old('title', $post->title) }}"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                    <x-ui.field label="Slug" name="slug" hint="Leave blank to derive it from the title.">
                        <input type="text" name="slug" value="{{ old('slug', $post->slug) }}"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                    <x-ui.field label="Excerpt" name="excerpt" hint="Shown in listings.">
                        <textarea name="excerpt" rows="2"
                                  class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">{{ old('excerpt', $post->excerpt) }}</textarea>
                    </x-ui.field>
                    <x-ui.field label="Body" name="body" hint="HTML is allowed and rendered on the article page.">
                        <textarea name="body" rows="14"
                                  class="mt-1.5 w-full rounded-lg border-ink-200 font-mono text-xs focus:border-brand-400 focus:ring-brand-400">{{ old('body', $post->body) }}</textarea>
                    </x-ui.field>
                </div>
            </x-ui.card>

            <x-ui.card title="Search appearance">
                <div class="space-y-4">
                    <x-ui.field label="SEO title" name="seo_title">
                        <input type="text" name="seo_title" maxlength="70" value="{{ old('seo_title', $post->seo_title) }}"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                    <x-ui.field label="Meta description" name="seo_description">
                        <textarea name="seo_description" rows="2" maxlength="180"
                                  class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">{{ old('seo_description', $post->seo_description) }}</textarea>
                    </x-ui.field>
                </div>
            </x-ui.card>
        </div>

        <div class="space-y-4">
            <x-ui.card title="Publishing">
                <div class="space-y-4">
                    <x-ui.field label="Status" name="status" required>
                        <select name="status" class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                            @foreach (['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $post->status) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </x-ui.field>
                    <x-ui.field label="Publish date" name="published_at">
                        <input type="datetime-local" name="published_at"
                               value="{{ old('published_at', $post->published_at?->format('Y-m-d\TH:i')) }}"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                    <x-ui.field label="Category" name="category_id">
                        <select name="category_id" class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                            <option value="">Uncategorised</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id', $post->category_id) == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </x-ui.field>
                    <x-ui.field label="Tags" name="tags" hint="Comma separated.">
                        <input type="text" name="tags" value="{{ old('tags', is_array($post->tags) ? implode(', ', $post->tags) : '') }}"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                </div>
            </x-ui.card>

            <div class="flex justify-end gap-2">
                <x-ui.button href="{{ route('admin.posts.index') }}" variant="secondary">Cancel</x-ui.button>
                <x-ui.button type="submit" icon="check">Save</x-ui.button>
            </div>

            @unless ($isNew)
                <x-ui.card title="Danger zone">
                    <x-ui.confirm-button :action="route('admin.posts.destroy', $post)" label="Delete article"
                                         message="Delete “{{ $post->title }}”?" />
                </x-ui.card>
            @endunless
        </div>
    </form>
</x-layouts.admin>
