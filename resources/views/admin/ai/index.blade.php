<x-layouts.admin title="AI sales agent" heading="AI sales agent" subheading="How the agent answers enquiries, and what it has said."
                :breadcrumbs="['Customers' => route('admin.ai.index')]">
    <div class="grid gap-4 sm:grid-cols-3">
        <x-ui.stat label="Replies sent" :value="number_format($stats['runs'])" />
        <x-ui.stat label="Escalated to staff" :value="number_format($stats['escalated'])" hint="Handed over automatically" />
        <x-ui.stat label="Average latency" :value="$stats['avg_latency'].' ms'" />
    </div>

    <div class="mt-4 grid gap-4 lg:grid-cols-3">
        <x-ui.card title="Behaviour" class="lg:col-span-2">
            <form method="POST" action="{{ route('admin.ai.update') }}" class="space-y-4">
                @csrf @method('PUT')

                <label class="flex items-start gap-2.5 rounded-lg border border-ink-150 p-3">
                    <input type="checkbox" name="ai_enabled" value="1" @checked(\App\Models\Setting::get('ai_enabled', '1') === '1')
                           class="mt-0.5 size-4 rounded border-ink-300 text-brand-600 focus:ring-brand-500">
                    <span class="text-sm">
                        <span class="font-medium text-ink-800">Auto-reply to new messages</span>
                        <span class="block text-xs text-ink-500">When off, every message waits for a person.</span>
                    </span>
                </label>

                @foreach ($settings->whereNotIn('key', ['ai_enabled']) as $setting)
                    <x-ui.field :label="$setting['label']" :name="$setting['key']">
                        <input type="{{ $setting['type'] === 'password' ? 'password' : 'text' }}"
                               name="{{ $setting['key'] }}" @if ($setting['type'] !== 'password') value="{{ $setting['value'] }}" @endif
                               @if ($setting['type'] === 'password') placeholder="Leave blank to keep the stored key" @endif
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                @endforeach

                <div class="rounded-lg bg-ink-50 p-3 text-[11px] leading-relaxed text-ink-600">
                    Without an API key the agent uses a deterministic rules engine: it recognises pricing, lead-time,
                    booking and complaint intents and answers from your live data. Adding a key switches it to the
                    configured model; the rules engine stays as the fallback if a request fails.
                </div>

                <x-ui.button type="submit" icon="check">Save agent settings</x-ui.button>
            </form>
        </x-ui.card>

        <div class="space-y-4">
            <x-ui.card title="Intents seen">
                @forelse ($intents as $intent => $total)
                    <div class="flex items-center justify-between py-1 text-sm">
                        <span class="text-ink-700">{{ \Illuminate\Support\Str::headline($intent ?: 'unknown') }}</span>
                        <span class="tabular-nums text-ink-500">{{ $total }}</span>
                    </div>
                @empty
                    <p class="py-4 text-center text-sm text-ink-500">No agent runs yet.</p>
                @endforelse
            </x-ui.card>

            <x-ui.card title="Model" :padding="false">
                <div class="px-4 py-2 text-xs">
                    <p class="text-ink-600">Provider: {{ \App\Models\Setting::get('ai_api_key') ? 'OpenAI' : 'Rules engine' }}</p>
                    <p class="mt-1 text-ink-500">Model: {{ \App\Models\Setting::get('ai_model', 'gpt-4o-mini') }}</p>
                </div>
            </x-ui.card>
        </div>
    </div>

    <x-ui.card title="Recent replies" class="mt-4" :padding="false">
        <div class="px-5 py-1">
            <x-ui.table :head="['When', 'Intent', 'Reply', 'Tokens', 'Status']">
                @forelse ($runs as $run)
                    <tr>
                        <td class="py-2.5 pl-0 pr-3 text-xs text-ink-500">{{ $run->created_at->format('j M, g:i A') }}</td>
                        <td class="px-3 py-2.5 text-xs text-ink-700">{{ \Illuminate\Support\Str::headline($run->intent ?? '—') }}</td>
                        <td class="max-w-md px-3 py-2.5 text-xs text-ink-600">{{ \Illuminate\Support\Str::limit($run->reply, 110) }}</td>
                        <td class="px-3 py-2.5 text-[11px] tabular-nums text-ink-500">{{ $run->prompt_tokens }} / {{ $run->completion_tokens }}</td>
                        <td class="px-3 py-2.5">
                            @if ($run->escalated)
                                <x-ui.badge tone="warning">Escalated</x-ui.badge>
                            @else
                                <x-ui.badge tone="success">Answered</x-ui.badge>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-6 text-center text-sm text-ink-500">The agent has not replied to anyone yet.</td></tr>
                @endforelse
            </x-ui.table>
        </div>
    </x-ui.card>
</x-layouts.admin>
