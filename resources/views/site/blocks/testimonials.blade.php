@php
    $attrs = $block['attrs'] ?? [];
    $testimonials = \App\Models\Testimonial::query()->where('is_published', true)
        ->orderBy('position')->limit((int) ($attrs['limit'] ?? 3))->get();
@endphp
<section class="mx-auto max-w-6xl px-5 py-6" data-block="testimonials">
    <h2 class="font-display text-2xl font-semibold tracking-tight text-ink-900" data-bind="heading">{{ $attrs['heading'] ?? '' }}</h2>
    <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($testimonials as $testimonial)
            <blockquote class="flex flex-col justify-between rounded-xl border border-ink-150 bg-white p-5">
                <div>
                    <div class="flex gap-0.5 text-brand-500">
                        @for ($i = 0; $i < (int) $testimonial->rating; $i++)
                            <x-nav-icon name="star" class="size-3.5 fill-current" />
                        @endfor
                    </div>
                    <p class="mt-3 text-sm leading-relaxed text-ink-700">“{{ $testimonial->quote }}”</p>
                </div>
                <footer class="mt-4 flex items-center gap-2.5">
                    <span class="grid size-8 place-items-center rounded-full bg-ink-100 text-[11px] font-semibold text-ink-600">
                        {{ \Illuminate\Support\Str::initials($testimonial->author) }}
                    </span>
                    <div>
                        <p class="text-xs font-semibold text-ink-900">{{ $testimonial->author }}</p>
                        <p class="text-[11px] text-ink-500">{{ $testimonial->role }}{{ $testimonial->company ? ' · '.$testimonial->company : '' }}</p>
                    </div>
                </footer>
            </blockquote>
        @endforeach
    </div>
</section>
