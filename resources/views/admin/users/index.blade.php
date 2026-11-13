<x-layouts.admin title="Team" heading="Team" subheading="Studio accounts and what each role can reach."
                :breadcrumbs="['System' => route('admin.users.index')]">
    <x-slot:actions>
        <x-ui.button href="{{ route('admin.users.create') }}" icon="plus">Invite member</x-ui.button>
    </x-slot:actions>

    <x-ui.card :padding="false">
        <div class="px-5 py-1">
            <x-ui.table :head="['Member', 'Role', 'Job title', 'Last sign-in', 'Status', '']">
                @foreach ($users as $user)
                    <tr>
                        <td class="py-3 pl-0 pr-3">
                            <div class="flex items-center gap-2.5">
                                <span class="grid size-8 place-items-center rounded-full bg-ink-100 text-[11px] font-semibold text-ink-600">{{ $user->initials() }}</span>
                                <div>
                                    <p class="text-sm font-medium text-ink-900">{{ $user->name }}</p>
                                    <p class="text-[11px] text-ink-500">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 py-3">
                            <x-ui.badge :tone="$user->isOwner() ? 'brand' : ($user->isAdmin() ? 'info' : 'neutral')">{{ $user->role->label() }}</x-ui.badge>
                        </td>
                        <td class="px-3 py-3 text-xs text-ink-600">{{ $user->job_title ?: '—' }}</td>
                        <td class="px-3 py-3 text-xs text-ink-500">{{ $user->last_login_at?->diffForHumans(short: true) ?? 'Never' }}</td>
                        <td class="px-3 py-3">
                            <x-ui.badge :tone="$user->is_active ? 'success' : 'danger'">{{ $user->is_active ? 'Active' : 'Disabled' }}</x-ui.badge>
                        </td>
                        <td class="py-3 pl-3 pr-0 text-right">
                            <x-ui.button href="{{ route('admin.users.edit', $user) }}" variant="ghost" icon="pencil">Edit</x-ui.button>
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>
        </div>
    </x-ui.card>
    <x-ui.pagination :paginator="$users" />

    <div class="mt-4 rounded-xl border border-ink-200 bg-white p-5">
        <h2 class="text-sm font-semibold text-ink-900">What each role can reach</h2>
        <dl class="mt-3 grid gap-3 text-xs sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <dt class="font-semibold text-brand-700">Owner</dt>
                <dd class="mt-0.5 text-ink-600">Everything, including granting owner access.</dd>
            </div>
            <div>
                <dt class="font-semibold text-ink-800">Administrator</dt>
                <dd class="mt-0.5 text-ink-600">All modules, settings, security and the team.</dd>
            </div>
            <div>
                <dt class="font-semibold text-ink-800">Manager</dt>
                <dd class="mt-0.5 text-ink-600">Operations, growth, purchasing, finance and the CMS.</dd>
            </div>
            <div>
                <dt class="font-semibold text-ink-800">Staff</dt>
                <dd class="mt-0.5 text-ink-600">Day-to-day: leads, orders, quotations, content and media.</dd>
            </div>
        </dl>
    </div>
</x-layouts.admin>
