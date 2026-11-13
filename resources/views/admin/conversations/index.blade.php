<x-layouts.admin title="Conversations" heading="Conversations" subheading="WhatsApp, live chat and web enquiries in one inbox."
                :breadcrumbs="['Customers' => route('admin.conversations.index')]">
    <div class="mb-4 grid gap-4 sm:grid-cols-3">
        <x-ui.stat label="Open" :value="$stats['open']" />
        <x-ui.stat label="Unread" :value="$stats['unread']" />
        <x-ui.stat label="Waiting for a human" :value="$stats['handover']" hint="Escalated by the AI agent" />
    </div>

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap gap-1.5">
            @foreach (['' => 'All', 'open' => 'Open', 'pending' => 'Needs attention', 'handed_over' => 'With staff', 'resolved' => 'Resolved'] as $value => $label)
                <a href="{{ route('admin.conversations.index', array_filter(['status' => $value])) }}"
                   @class([
                       'rounded-full px-3 py-1.5 text-xs font-medium',
                       'bg-ink-900 text-white' => request('status', '') === $value,
                       'bg-white text-ink-600 ring-1 ring-inset ring-ink-200 hover:bg-ink-50' => request('status', '') !== $value,
                   ])>{{ $label }}</a>
            @endforeach
        </div>
        <div class="flex gap-1.5">
            @foreach (['' => 'All channels', 'whatsapp' => 'WhatsApp', 'web_chat' => 'Live chat', 'email' => 'Email'] as $value => $label)
                <a href="{{ route('admin.conversations.index', array_filter(['channel' => $value, 'status' => request('status')])) }}"
                   @class([
                       'rounded-full px-2.5 py-1 text-[11px] font-medium',
                       'bg-brand-600 text-white' => request('channel', '') === $value,
                       'bg-white text-ink-500 ring-1 ring-inset ring-ink-200' => request('channel', '') !== $value,
                   ])>{{ $label }}</a>
            @endforeach
        </div>
    </div>

    @if ($conversations->isEmpty())
        <x-ui.empty title="No conversations yet" description="Messages from the website chat and WhatsApp land here." icon="chat" />
    @else
        <div class="overflow-hidden rounded-xl border border-ink-200 bg-white shadow-panel">
            <ul class="divide-y divide-ink-100">
                @foreach ($conversations as $conversation)
                    <li>
                        <a href="{{ route('admin.conversations.show', $conversation) }}"
                           class="flex items-start gap-3 px-4 py-3 transition hover:bg-ink-50">
                            <span class="grid size-9 shrink-0 place-items-center rounded-full bg-ink-100 text-ink-600">
                                <x-nav-icon :name="$conversation->channel === 'whatsapp' ? 'phone' : 'chat'" class="size-4" />
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <p class="truncate text-sm font-medium text-ink-900">
                                        {{ $conversation->guest_name ?: ($conversation->customer?->name ?? 'Guest') }}
                                    </p>
                                    <span class="text-[10px] uppercase tracking-wide text-ink-400">{{ str_replace('_', ' ', $conversation->channel) }}</span>
                                    @if ($conversation->unread_for_staff > 0)
                                        <x-ui.badge tone="brand">{{ $conversation->unread_for_staff }} new</x-ui.badge>
                                    @endif
                                </div>
                                <p class="truncate text-xs text-ink-500">{{ $conversation->subject ?: $conversation->latestMessage->first()?->body ?: 'No messages yet' }}</p>
                            </div>
                            <div class="shrink-0 text-right">
                                <x-ui.status :status="$conversation->status" />
                                <p class="mt-1 text-[11px] text-ink-400">{{ $conversation->last_message_at?->diffForHumans(short: true) }}</p>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
        <x-ui.pagination :paginator="$conversations" />
    @endif
</x-layouts.admin>
