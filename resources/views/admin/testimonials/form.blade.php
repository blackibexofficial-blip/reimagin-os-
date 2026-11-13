@php $isNew = ! $testimonial->exists; @endphp
<x-layouts.admin :title="$isNew ? 'New testimonial' : 'Edit testimonial'" :heading="$isNew ? 'New testimonial' : 'Edit testimonial'"
                 :breadcrumbs="['Testimonials' => route('admin.testimonials.index')]">
    <form method="POST" action="{{ $isNew ? route('admin.testimonials.store') : route('admin.testimonials.update', $testimonial) }}"
          class="grid gap-4 lg:grid-cols-3">
        @csrf
        @unless ($isNew) @method('PUT') @endunless

        <div class="space-y-4 lg:col-span-2">
            <x-ui.card title="Quote">
                <div class="space-y-4">
                    <x-ui.field label="Quote" name="quote" required>
                        <textarea name="quote" rows="4" required
                                  class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">{{ old('quote', $testimonial->quote) }}</textarea>
                    </x-ui.field>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <x-ui.field label="Author" name="author" required>
                            <input type="text" name="author" required value="{{ old('author', $testimonial->author) }}"
                                   class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                        </x-ui.field>
                        <x-ui.field label="Role" name="role">
                            <input type="text" name="role" value="{{ old('role', $testimonial->role) }}"
                                   class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                        </x-ui.field>
                        <x-ui.field label="Company" name="company">
                            <input type="text" name="company" value="{{ old('company', $testimonial->company) }}"
                                   class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                        </x-ui.field>
                    </div>
                </div>
            </x-ui.card>
        </div>

        <div class="space-y-4">
            <x-ui.card title="Display">
                <div class="space-y-4">
                    <x-ui.field label="Rating" name="rating">
                        <select name="rating" class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                            @foreach (range(5, 1) as $rating)
                                <option value="{{ $rating }}" @selected(old('rating', $testimonial->rating) == $rating)>{{ $rating }} star{{ $rating > 1 ? 's' : '' }}</option>
                            @endforeach
                        </select>
                    </x-ui.field>
                    <x-ui.field label="Sort order" name="position">
                        <input type="number" name="position" min="0" value="{{ old('position', $testimonial->position) }}"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                    <label class="flex items-center gap-2.5 text-sm text-ink-800">
                        <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $testimonial->is_published))
                               class="size-4 rounded border-ink-300 text-brand-600 focus:ring-brand-500">
                        Publish on the site
                    </label>
                </div>
            </x-ui.card>

            <div class="flex justify-end gap-2">
                <x-ui.button href="{{ route('admin.testimonials.index') }}" variant="secondary">Cancel</x-ui.button>
                <x-ui.button type="submit" icon="check">Save</x-ui.button>
            </div>

            @unless ($isNew)
                <x-ui.card title="Danger zone">
                    <x-ui.confirm-button :action="route('admin.testimonials.destroy', $testimonial)" label="Delete testimonial"
                                         message="Delete this testimonial?" />
                </x-ui.card>
            @endunless
        </div>
    </form>
</x-layouts.admin>
