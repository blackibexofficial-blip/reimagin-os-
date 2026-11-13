<x-layouts.admin title="Security" heading="Security" subheading="Access checks, active sessions and recent activity."
                :breadcrumbs="['System' => route('admin.security.index')]">
    <div class="grid gap-4 lg:grid-cols-3">
        <x-ui.card title="Posture checks" class="lg:col-span-1">
            <ul class="space-y-2.5">
                @foreach ($checks as $check)
                    <li class="flex items-start gap-2.5">
                        <span @class([
                            'mt-0.5 grid size-5 shrink-0 place-items-center rounded-full',
                            'bg-emerald-100 text-emerald-700' => $check['ok'],
                            'bg-amber-100 text-amber-700' => ! $check['ok'],
                        ])>
                            <x-nav-icon :name="$check['ok'] ? 'check' : 'shield'" class="size-3" />
                        </span>
                        <div>
                            <p class="text-sm text-ink-800">{{ $check['label'] }}</p>
                            <p class="text-[11px] text-ink-500">{{ $check['detail'] }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        </x-ui.card>

        <x-ui.card title="Active sessions" description="Last seven days" class="lg:col-span-2" :padding="false">
            <div class="px-5 py-1">
                <x-ui.table :head="['User', 'IP', 'Device', 'Last seen', '']">
                    @forelse ($sessions as $session)
                        <tr>
                            <td class="py-2.5 pl-0 pr-3 text-sm text-ink-800">{{ $session['user'] }}</td>
                            <td class="px-3 py-2.5 font-mono text-[11px] text-ink-500">{{ $session['ip'] }}</td>
                            <td class="px-3 py-2.5 text-[11px] text-ink-500">{{ \Illuminate\Support\Str::limit($session['agent'], 48) }}</td>
                            <td class="px-3 py-2.5 text-xs text-ink-500">{{ $session['last_activity']->diffForHumans(short: true) }}</td>
                            <td class="py-2.5 pl-3 pr-0 text-right">
                                <form method="POST" action="{{ route('admin.security.sessions.destroy', $session['id']) }}"
                                      x-data @submit.prevent="if (confirm('Revoke this session?')) $el.submit()">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-[11px] font-medium text-red-600 hover:text-red-700">Revoke</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-6 text-center text-sm text-ink-500">No recent sessions.</td></tr>
                    @endforelse
                </x-ui.table>
            </div>
        </x-ui.card>
    </div>

    <div class="mt-4 grid gap-4 lg:grid-cols-2">
        <x-ui.card title="Recent activity" :padding="false">
            <div class="max-h-80 overflow-y-auto px-5 py-1">
                @forelse ($recent as $activity)
                    <div class="border-b border-ink-100 py-2.5 last:border-0">
                        <p class="text-sm text-ink-800">{{ $activity->title }}</p>
                        <p class="text-[11px] text-ink-500">
                            {{ $activity->user?->name ?? 'System' }} · {{ $activity->created_at->format('j M, g:i A') }}
                        </p>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-ink-500">Nothing logged yet.</p>
                @endforelse
            </div>
        </x-ui.card>

        <x-ui.card title="Sign-in history" :padding="false">
            <div class="px-5 py-1">
                @foreach ($users as $user)
                    <div class="flex items-center justify-between gap-2 border-b border-ink-100 py-2.5 last:border-0">
                        <div>
                            <p class="text-sm text-ink-800">{{ $user->name }}</p>
                            <p class="text-[11px] text-ink-500">{{ $user->role->label() }}</p>
                        </div>
                        <span class="text-xs text-ink-500">{{ $user->last_login_at?->diffForHumans(short: true) ?? 'Never signed in' }}</span>
                    </div>
                @endforeach
            </div>
        </x-ui.card>
    </div>
</x-layouts.admin>
