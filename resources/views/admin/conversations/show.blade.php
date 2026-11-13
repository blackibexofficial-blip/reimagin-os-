<x-layouts.admin :title="'Conversation'" heading="Conversation"
                 :subheading="$conversation->guest_name ?: ($conversation->customer?->name ?? 'Guest')"
                 :breadcrumbs="['Conversations' => route('admin.conversations.index')]">
    <x-slot:actions>
        <x-ui.status :status="$conversation->status" />
    </x-slot:actions>

    <div class="grid gap-4 lg:grid-cols-3">
        <x-ui.card class="lg:col-span-2" title="Messages" :padding="false">
            <div class="max-h-[32rem] space-y-3 overflow-y-auto px-5 py-4">
                @forelse ($conversation->messages as $message)
                    <div @class([
                        'max-w-[85%] rounded-2xl px-3.5 py-2.5 text-sm',
                        'ml-auto bg-ink-900 text-white' => $message->author_type === 'staff',
                        'bg-ink-100 text-ink-800' => $message->author_type === 'customer',
                        'border border-brand-200 bg-brand-50 text-brand-900' => $message->author_type === 'agent',
                    ])>
                        <p @class(['mb-1 text-[10px] font-semibold uppercase tracking-wide', 'text-brand-700' => $message->author_type === 'agent', 'text-ink-400' => $message->author_type !== 'agent'])>
                            {{ $message->author_type === 'staff' ? ($message->user?->name ?? 'Staff') : \Illuminate\Support\Str::headline($message->author_type) }}
                            @if (($message->meta['drafted_by'] ?? null) === 'ai_agent') · AI draft @endif
                        </p>
                        <p class="whitespace-pre-line">{{ $message->body }}</p>
                        <p @class(['mt-1 text-[10px]', 'text-white/50' => $message->author_type === 'staff', 'text-ink-400' => $message->author_type !== 'staff'])>
                            {{ $message->created_at->format('j M, g:i A') }}
                        </p>
                    </div>
                @empty
                    <p class="py-10 text-center text-sm text-ink-500">No messages in this thread yet.</p>
                @endforelse
            </div>
        </x-ui.card>

        <div class="space-y-4">
            <x-ui.card title="Reply">
                <form method="POST" action="{{ route('admin.conversations.reply', $conversation) }}" class="space-y-3">
                    @csrf
                    <textarea name="body" rows="5" required placeholder="Type a reply…"
                              class="w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400"></textarea>
                    <label class="flex items-start gap-2 text-xs text-ink-600">
                        <input type="checkbox" name="use_ai" value="1"
                               class="mt-0.5 size-4 rounded border-ink-300 text-brand-600 focus:ring-brand-500">
                        <span>Ask the AI agent for a draft reply instead of sending mine (the draft appears above for review).</span>
                    </label>
                    <x-ui.button type="submit" class="w-full" icon="mail">Post reply</x-ui.button>
                </form>
            </x-ui.card>

            <x-ui.card title="Status">
                <form method="POST" action="{{ route('admin.conversations.status', $conversation) }}" class="space-y-2">
                    @csrf
                    <select name="status" class="w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                        @foreach (['open' => 'Open', 'handed_over' => 'With staff', 'resolved' => 'Resolved'] as $value => $label)
                            <option value="{{ $value }}" @selected($conversation->status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-ui.button type="submit" variant="secondary" class="w-full">Update</x-ui.button>
                </form>
            </x-ui.card>

            <x-ui.card title="Contact">
                <dl class="space-y-2 text-sm">
                    <div><dt class="text-[11px] uppercase tracking-wide text-ink-400">Channel</dt><dd>{{ \Illuminate\Support\Str::headline($conversation->channel) }}</dd></div>
                    <div><dt class="text-[11px] uppercase tracking-wide text-ink-400">Guest</dt><dd>{{ $conversation->guest_name ?: '—' }}</dd></div>
                    <div><dt class="text-[11px] uppercase tracking-wide text-ink-400">Phone</dt><dd>{{ $conversation->guest_phone ?: '—' }}</dd></div>
                    <div><dt class="text-[11px] uppercase tracking-wide text-ink-400">Email</dt><dd>{{ $conversation->guest_email ?: '—' }}</dd></div>
                </dl>
                @if ($conversation->customer)
                    <a href="{{ route('admin.customers.show', $conversation->customer) }}" class="mt-3 inline-block text-xs font-medium text-brand-700">Open customer →</a>
                @endif
            </x-ui.card>

            <x-ui.card title="Agent runs" :padding="false">
                <div class="px-4 py-2">
                    @foreach ($conversation->runs as $run)
                        <div class="flex items-center justify-between gap-2 border-b border-ink-100 py-2 text-xs last:border-0">
                            <span class="text-ink-600">{{ \Illuminate\Support\Str::headline($run->intent ?? 'unknown') }}</span>
                            <span class="text-ink-400">{{ $run->latency_ms }}ms</span>
                        </div>
                    @endforeach
                    @if ($conversation->runs->isEmpty())
                        <p class="py-3 text-center text-xs text-ink-500">The agent has not replied yet.</p>
                    @endif
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.admin>
