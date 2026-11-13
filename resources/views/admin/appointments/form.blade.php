@php $isNew = ! $appointment->exists; @endphp
<x-layouts.admin :title="$isNew ? 'Book appointment' : 'Edit appointment'" :heading="$isNew ? 'Book appointment' : 'Edit appointment'"
                 :subheading="$isNew ? null : $appointment->reference"
                 :breadcrumbs="['Appointments' => route('admin.appointments.index')]">
    <form method="POST" action="{{ $isNew ? route('admin.appointments.store') : route('admin.appointments.update', $appointment) }}"
          class="grid gap-4 lg:grid-cols-3">
        @csrf
        @unless ($isNew) @method('PUT') @endunless

        <div class="space-y-4 lg:col-span-2">
            <x-ui.card title="Visitor">
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.field label="Name" name="name" required>
                        <input type="text" name="name" required value="{{ old('name', $appointment->name) }}"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                    <x-ui.field label="Phone" name="phone">
                        <input type="text" name="phone" value="{{ old('phone', $appointment->phone) }}"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                    <x-ui.field label="Email" name="email">
                        <input type="email" name="email" value="{{ old('email', $appointment->email) }}"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                    <x-ui.field label="Type" name="type">
                        <select name="type" class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                            @foreach (['showroom' => 'Showroom visit', 'site_survey' => 'Site survey', 'consultation' => 'Design consultation'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('type', $appointment->type) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </x-ui.field>
                    <x-ui.field label="Starts at" name="starts_at" required>
                        <input type="datetime-local" name="starts_at" required
                               value="{{ old('starts_at', $appointment->starts_at?->format('Y-m-d\TH:i')) }}"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                        @error('starts_at')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                    </x-ui.field>
                    <x-ui.field label="Host" name="staff_id">
                        <select name="staff_id" class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                            <option value="">Anyone available</option>
                            @foreach ($staff as $member)
                                <option value="{{ $member->id }}" @selected(old('staff_id', $appointment->staff_id) == $member->id)>{{ $member->name }}</option>
                            @endforeach
                        </select>
                    </x-ui.field>
                    <x-ui.field label="Location" name="location">
                        <input type="text" name="location" value="{{ old('location', $appointment->location) }}"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                    @unless ($isNew)
                        <x-ui.field label="Status" name="status">
                            <select name="status" class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                                @foreach (['requested', 'confirmed', 'completed', 'cancelled', 'no_show'] as $status)
                                    <option value="{{ $status }}" @selected($appointment->status === $status)>{{ \Illuminate\Support\Str::headline($status) }}</option>
                                @endforeach
                            </select>
                        </x-ui.field>
                    @endunless
                </div>
                <x-ui.field label="Notes" name="notes" class="mt-4">
                    <textarea name="notes" rows="3"
                              class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">{{ old('notes', $appointment->notes) }}</textarea>
                </x-ui.field>
            </x-ui.card>
        </div>

        <div class="space-y-4">
            <x-ui.card title="Save">
                <p class="text-xs text-ink-500">New bookings start as <em>requested</em>; confirm once the visitor has been called.</p>
                <div class="mt-3 flex justify-end gap-2">
                    <x-ui.button href="{{ route('admin.appointments.index') }}" variant="secondary">Cancel</x-ui.button>
                    <x-ui.button type="submit" icon="check">{{ $isNew ? 'Book' : 'Save' }}</x-ui.button>
                </div>
            </x-ui.card>

            @unless ($isNew)
                <x-ui.card title="Danger zone">
                    <x-ui.confirm-button :action="route('admin.appointments.destroy', $appointment)" label="Cancel appointment"
                                         message="Cancel this appointment?" />
                </x-ui.card>
            @endunless
        </div>
    </form>
</x-layouts.admin>
