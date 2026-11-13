@php
    $attrs = $block['attrs'] ?? [];
    $media = filled($attrs['media_id'] ?? null) ? \App\Models\Media::find($attrs['media_id']) : null;
    $ratio = ['square' => 'aspect-square', 'wide' => 'aspect-[16/9]', 'portrait' => 'aspect-[4/5]', 'natural' => ''][$attrs['ratio'] ?? 'wide'] ?? 'aspect-[16/9]';
@endphp
<figure class="mx-auto max-w-6xl px-5" data-block="image">
    <div class="overflow-hidden rounded-xl bg-ink-100 {{ $ratio }}">
        @if ($media)
            <img src="{{ $media->url() }}" alt="{{ $media->alt_text ?: $media->name }}"
                 class="size-full object-cover" data-bind="media_id" loading="lazy">
        @else
            <div class="grid size-full place-items-center text-xs text-ink-400">Choose an image</div>
        @endif
    </div>
    @if (filled($attrs['caption'] ?? null))
        <figcaption class="mt-2 text-center text-xs text-ink-500" data-bind="caption">{{ $attrs['caption'] }}</figcaption>
    @endif
</figure>
