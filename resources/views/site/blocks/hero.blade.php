@php
    $attrs = $block['attrs'] ?? [];
    $media = filled($attrs['media_id'] ?? null) ? \App\Models\Media::find($attrs['media_id']) : null;
    $align = ($attrs['align'] ?? 'left') === 'center' ? 'text-center items-center' : 'text-left items-start';
@endphp
<section class="relative overflow-hidden bg-ink-950 text-white" data-block="hero">
    @if ($media)
        <img src="{{ $media->url() }}" alt="" class="absolute inset-0 size-full object-cover opacity-40" data-bind="media_id" loading="eager">
    @endif
    <div class="absolute inset-0 bg-gradient-to-br from-ink-950 via-ink-950/80 to-transparent"></div>
    <div class="relative mx-auto flex min-h-[26rem] max-w-6xl flex-col justify-center px-5 py-20 {{ $align }}">
        <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-brand-400" data-bind="eyebrow">{{ $attrs['eyebrow'] ?? '' }}</p>
        <h1 class="mt-3 max-w-3xl font-display text-4xl leading-tight font-semibold tracking-tight sm:text-5xl" data-bind="heading">
            {{ $attrs['heading'] ?? '' }}
        </h1>
        <p class="mt-4 max-w-xl text-base leading-relaxed text-ink-300" data-bind="body">{{ $attrs['body'] ?? '' }}</p>
        <div class="mt-8 flex flex-wrap gap-3 {{ ($attrs['align'] ?? 'left') === 'center' ? 'justify-center' : '' }}">
            @if (filled($attrs['cta_label'] ?? null))
                <a href="{{ $attrs['cta_url'] ?? '#' }}" data-href="cta_url"
                   class="inline-flex items-center gap-2 rounded-full bg-brand-500 px-6 py-3 text-sm font-semibold text-ink-950 hover:bg-brand-400">
                    <span data-bind="cta_label">{{ $attrs['cta_label'] }}</span>
                </a>
            @endif
            @if (filled($attrs['secondary_label'] ?? null))
                <a href="{{ $attrs['secondary_url'] ?? '#' }}" data-href="secondary_url"
                   class="inline-flex items-center gap-2 rounded-full border border-white/25 px-6 py-3 text-sm font-semibold text-white hover:bg-white/10">
                    <span data-bind="secondary_label">{{ $attrs['secondary_label'] }}</span>
                </a>
            @endif
        </div>
    </div>
</section>
