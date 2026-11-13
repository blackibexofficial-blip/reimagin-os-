@php
    $html = \App\Support\Blocks\BlockRegistry::sanitizeHtml((string) ($block['attrs']['html'] ?? ''));
@endphp
<div class="mx-auto max-w-6xl px-5" data-block="html">
    @if ($html !== '')
        {!! $html !!}
    @else
        <div class="rounded-lg border border-dashed border-ink-300 p-6 text-center text-xs text-ink-400">Custom HTML</div>
    @endif
</div>
