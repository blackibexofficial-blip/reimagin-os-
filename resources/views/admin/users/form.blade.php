@php $isNew = ! $user->exists; @endphp
<x-layouts.admin :title="$isNew ? 'Invite member' : 'Edit '.$user->name" :heading="$isNew ? 'Invite member' : 'Edit member'"
                 :breadcrumbs="['Team' => route('admin.users.index')]">
    <form method="POST" action="{{ $isNew ? route('admin.users.store') : route('admin.users.update', $user) }}"
          class="grid gap-4 lg:grid-cols-3">
        @csrf
        @unless ($isNew) @method('PUT') @endunless

        <div class="space-y-4 lg:col-span-2">
            <x-ui.card title="Account">
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.field label="Name" name="name" required>
                        <input type="text" name="name" required value="{{ old('name', $user->name) }}"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                    <x-ui.field label="Email" name="email" required>
                        <input type="email" name="email" required value="{{ old('email', $user->email) }}"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                    <x-ui.field label="Phone" name="phone">
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                    <x-ui.field label="Job title" name="job_title">
                        <input type="text" name="job_title" value="{{ old('job_title', $user->job_title) }}"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                </div>
            </x-ui.card>

            <x-ui.card title="Password" :description="$isNew ? 'Minimum ten characters.' : 'Leave blank to keep the current password.'">
                <x-ui.field label="Password" name="password" :required="$isNew">
                    <input type="password" name="password" @required($isNew) autocomplete="new-password"
                           class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                </x-ui.field>
            </x-ui.card>
        </div>

        <div class="space-y-4">
            <x-ui.card title="Access">
                <div class="space-y-4">
                    <x-ui.field label="Role" name="role" required>
                        <select name="role" class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                            @foreach ($roles as $role)
                                <option value="{{ $role->value }}" @selected(old('role', $user->role?->value) === $role->value)>{{ $role->label() }}</option>
                            @endforeach
                        </select>
                    </x-ui.field>
                    <label class="flex items-center gap-2.5 text-sm text-ink-800">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active))
                               class="size-4 rounded border-ink-300 text-brand-600 focus:ring-brand-500">
                        Can sign in
                    </label>
                </div>
            </x-ui.card>

            <div class="flex justify-end gap-2">
                <x-ui.button href="{{ route('admin.users.index') }}" variant="secondary">Cancel</x-ui.button>
                <x-ui.button type="submit" icon="check">{{ $isNew ? 'Send invite' : 'Save' }}</x-ui.button>
            </div>

            @unless ($isNew)
                <x-ui.card title="Danger zone">
                    <p class="mb-2 text-xs text-ink-500">Accounts are disabled rather than deleted, so order history stays attributed.</p>
                    <x-ui.confirm-button :action="route('admin.users.destroy', $user)" label="Disable account"
                                         message="Disable {{ $user->name }}? They will be signed out and cannot sign in again." />
                </x-ui.card>
            @endunless
        </div>
    </form>
</x-layouts.admin>
