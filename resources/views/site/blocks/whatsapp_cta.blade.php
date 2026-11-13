@php
    $attrs = $block['attrs'] ?? [];
    $number = preg_replace('/\D+/', '', (string) ($attrs['number'] ?? settings('whatsapp_number', '923004567890')));
@endphp
<section class="mx-auto max-w-6xl px-5 py-4" data-block="whatsapp_cta">
    <div class="flex flex-col items-start justify-between gap-5 rounded-2xl bg-ink-950 p-6 text-white sm:flex-row sm:items-center sm:p-8">
        <div>
            <h2 class="font-display text-xl font-semibold tracking-tight" data-bind="heading">{{ $attrs['heading'] ?? 'Talk to a designer' }}</h2>
            <p class="mt-1 max-w-lg text-sm text-ink-300" data-bind="body">{{ $attrs['body'] ?? '' }}</p>
        </div>
        <a href="https://wa.me/{{ $number }}" target="_blank" rel="noopener"
           class="inline-flex shrink-0 items-center gap-2 rounded-full bg-emerald-500 px-5 py-3 text-sm font-semibold text-ink-950 hover:bg-emerald-400">
            <x-nav-icon name="chat" class="size-4" />
            <span data-bind="button_label">{{ $attrs['button_label'] ?? 'Chat on WhatsApp' }}</span>
        </a>
    </div>
</section>
