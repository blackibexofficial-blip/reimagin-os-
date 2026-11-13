@php $isNew = ! $page->exists; @endphp
<x-layouts.admin :title="$isNew ? 'New page' : $page->title"
                 :heading="$isNew ? 'New page' : 'Page settings'"
                 :subheading="$isNew ? 'Give the page a home, then build it block by block.' : 'Titles, address, template and search appearance.'"
                 :breadcrumbs="['Pages' => route('admin.pages.index')]">
    <x-slot:actions>
        @unless ($isNew)
            <x-ui.button href="{{ route('admin.pages.builder', $page) }}" variant="secondary" icon="layers">Open builder</x-ui.button>
        @endunless
    </x-slot:actions>

    <form method="POST"
          action="{{ $isNew ? route('admin.pages.store') : route('admin.pages.update', $page) }}"
          class="grid gap-4 lg:grid-cols-3">
        @csrf
        @unless ($isNew) @method('PUT') @endunless

        <div class="space-y-4 lg:col-span-2">
            <x-ui.card title="Content details">
                <div class="space-y-4">
                    <x-ui.field label="Title" name="title" required>
                        <input type="text" name="title" id="title" required autofocus
                               value="{{ old('title', $page->title) }}"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>

                    <x-ui.field label="Excerpt" name="excerpt" hint="One line used in listings and social cards.">
                        <textarea name="excerpt" id="excerpt" rows="2"
                                  class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">{{ old('excerpt', $page->excerpt) }}</textarea>
                    </x-ui.field>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-ui.field label="Address" name="slug" hint="Leave blank to derive it from the title.">
                            <div class="mt-1.5 flex rounded-lg border border-ink-200 bg-white focus-within:border-brand-400">
                                <span class="grid place-items-center rounded-l-lg border-r border-ink-200 bg-ink-50 px-3 text-sm text-ink-500">/</span>
                                <input type="text" name="slug" id="slug" value="{{ old('slug', $page->slug) }}"
                                       class="w-full rounded-r-lg border-0 text-sm focus:ring-0">
                            </div>
                        </x-ui.field>

                        <x-ui.field label="Template" name="template">
                            <select name="template" id="template"
                                    class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                                @foreach (['default' => 'Standard', 'landing' => 'Landing (no header)', 'full-width' => 'Full width'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('template', $page->template) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </x-ui.field>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card title="Search appearance" description="Shown in Google results and when the page is shared.">
                <div class="space-y-4">
                    <x-ui.field label="SEO title" name="seo_title">
                        <input type="text" name="seo_title" id="seo_title" maxlength="70"
                               value="{{ old('seo_title', $page->seo_title) }}"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                    <x-ui.field label="Meta description" name="seo_description">
                        <textarea name="seo_description" id="seo_description" rows="3" maxlength="180"
                                  class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">{{ old('seo_description', $page->seo_description) }}</textarea>
                    </x-ui.field>
                </div>
            </x-ui.card>
        </div>

        <div class="space-y-4">
            <x-ui.card title="Publishing">
                <div class="space-y-4">
                    <x-ui.field label="Status" name="status" required>
                        <select name="status" id="status"
                                class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                            @foreach (['draft' => 'Draft', 'published' => 'Published', 'scheduled' => 'Scheduled', 'archived' => 'Archived'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $page->status) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </x-ui.field>

                    <x-ui.field label="Publish at" name="scheduled_for" hint="Only used when the status is Scheduled.">
                        <input type="datetime-local" name="scheduled_for" id="scheduled_for"
                               value="{{ old('scheduled_for', $page->scheduled_for?->format('Y-m-d\TH:i')) }}"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>

                    <label class="flex items-start gap-2.5 rounded-lg border border-ink-150 p-3">
                        <input type="checkbox" name="is_homepage" value="1" @checked(old('is_homepage', $page->is_homepage))
                               class="mt-0.5 size-4 rounded border-ink-300 text-brand-600 focus:ring-brand-500">
                        <span class="text-sm">
                            <span class="font-medium text-ink-800">Use as homepage</span>
                            <span class="block text-xs text-ink-500">Replaces whichever page is currently the homepage.</span>
                        </span>
                    </label>

                    <label class="flex items-start gap-2.5 rounded-lg border border-ink-150 p-3">
                        <input type="checkbox" name="show_in_navigation" value="1" @checked(old('show_in_navigation', $page->show_in_navigation))
                               class="mt-0.5 size-4 rounded border-ink-300 text-brand-600 focus:ring-brand-500">
                        <span class="text-sm">
                            <span class="font-medium text-ink-800">Offer in navigation</span>
                            <span class="block text-xs text-ink-500">Adds the page to the main menu.</span>
                        </span>
                    </label>
                </div>
            </x-ui.card>

            <div class="flex items-center justify-end gap-2">
                <x-ui.button href="{{ route('admin.pages.index') }}" variant="secondary">Cancel</x-ui.button>
                <x-ui.button type="submit" icon="check">{{ $isNew ? 'Create page' : 'Save changes' }}</x-ui.button>
            </div>

            @unless ($isNew)
                <x-ui.card title="Danger zone" :padding="true">
                    <div class="space-y-3">
                        <p class="text-xs text-ink-500">
                            Deleting moves the page to the archive; revisions are kept so nothing is lost silently.
                        </p>
                        <x-ui.confirm-button :action="route('admin.pages.destroy', $page)" label="Delete page"
                                             message="Delete “{{ $page->title }}”?" />
                    </div>
                </x-ui.card>
            @endunless
        </div>
    </form>
</x-layouts.admin>
