@php
    $attrs = $block['attrs'] ?? [];
    $items = is_array($attrs['items'] ?? null) ? $attrs['items'] : [];
@endphp
<section class="mx-auto max-w-3xl px-5 py-4" data-block="faq" x-data="{ open: null }">
    <h2 class="font-display text-2xl font-semibold tracking-tight text-ink-900" data-bind="heading">{{ $attrs['heading'] ?? '' }}</h2>
    <dl class="mt-6 divide-y divide-ink-150 border-y border-ink-150">
        @foreach ($items as $index => $item)
            <div class="py-4">
                <dt>
                    <button type="button" @click="open = open === {{ $index }} ? null : {{ $index }}"
                            class="flex w-full items-start justify-between gap-4 text-left">
                        <span class="text-sm font-medium text-ink-900">{{ $item['question'] ?? '' }}</span>
                        <x-nav-icon name="chevron-down" class="mt-0.5 size-4 shrink-0 text-ink-400 transition"
                                    ::class="open === {{ $index }} ? 'rotate-180' : ''" />
                    </button>
                </dt>
                <dd x-show="open === {{ $index }}" x-collapse class="mt-2 pr-8 text-sm leading-relaxed text-ink-600">
                    {{ $item['answer'] ?? '' }}
                </dd>
            </div>
        @endforeach
    </dl>
</section>
