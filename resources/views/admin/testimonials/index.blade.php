<x-layouts.admin title="Testimonials" heading="Testimonials" subheading="Client words shown on the storefront."
                :breadcrumbs="['Website' => route('admin.testimonials.index')]">
    <x-slot:actions>
        <x-ui.button href="{{ route('admin.testimonials.create') }}" icon="plus">New testimonial</x-ui.button>
    </x-slot:actions>

    @if ($testimonials->isEmpty())
        <x-ui.empty title="No testimonials yet" description="Add a few words from a happy client." icon="star">
            <x-slot:action><x-ui.button href="{{ route('admin.testimonials.create') }}" icon="plus">Add one</x-ui.button></x-slot:action>
        </x-ui.empty>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($testimonials as $testimonial)
                <div class="flex flex-col rounded-xl border border-ink-200 bg-white p-4 shadow-panel">
                    <div class="flex items-center justify-between">
                        <div class="flex gap-0.5 text-brand-500">
                            @for ($i = 0; $i < (int) $testimonial->rating; $i++)
                                <x-nav-icon name="star" class="size-3 fill-current" />
                            @endfor
                        </div>
                        <x-ui.badge :tone="$testimonial->is_published ? 'success' : 'neutral'">
                            {{ $testimonial->is_published ? 'Published' : 'Hidden' }}
                        </x-ui.badge>
                    </div>
                    <p class="mt-2 flex-1 text-sm leading-relaxed text-ink-700">“{{ $testimonial->quote }}”</p>
                    <div class="mt-3 flex items-center justify-between border-t border-ink-100 pt-3">
                        <div>
                            <p class="text-xs font-semibold text-ink-900">{{ $testimonial->author }}</p>
                            <p class="text-[11px] text-ink-500">{{ $testimonial->role }}{{ $testimonial->company ? ' · '.$testimonial->company : '' }}</p>
                        </div>
                        <x-ui.button href="{{ route('admin.testimonials.edit', $testimonial) }}" variant="ghost" icon="pencil">Edit</x-ui.button>
                    </div>
                </div>
            @endforeach
        </div>
        <x-ui.pagination :paginator="$testimonials" />
    @endif
</x-layouts.admin>
