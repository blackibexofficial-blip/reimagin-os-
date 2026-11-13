<x-layouts.admin title="Appointments" heading="Appointments" subheading="Showroom visits, site surveys and consultations."
                :breadcrumbs="['Commerce' => route('admin.appointments.index')]">
    <x-slot:actions>
        <x-ui.button href="{{ route('admin.appointments.create') }}" icon="plus">Book appointment</x-ui.button>
    </x-slot:actions>

    <div class="grid gap-4 lg:grid-cols-3">
        <x-ui.card title="Free slots" :description="$day->format('l j F')" class="lg:col-span-1">
            <form method="GET" class="mb-3">
                <input type="date" name="date" value="{{ $day->format('Y-m-d') }}" onchange="this.form.submit()"
                       class="w-full rounded-lg border-ink-200 py-1.5 text-xs focus:border-brand-400 focus:ring-brand-400">
            </form>
            @if (count($slots) === 0)
                <p class="text-sm text-ink-500">The day is fully booked.</p>
            @else
                <div class="grid grid-cols-3 gap-1.5">
                    @foreach ($slots as $slot)
                        <span class="rounded-md bg-emerald-50 px-2 py-1 text-center text-[11px] font-medium text-emerald-800">
                            {{ $slot->format('g:i A') }}
                        </span>
                    @endforeach
                </div>
            @endif
            <p class="mt-3 text-[11px] text-ink-500">Slots are 45 minutes, 11:00–19:00. Bookings that overlap are rejected automatically.</p>
        </x-ui.card>

        <div class="lg:col-span-2">
            <div class="mb-3 flex flex-wrap gap-1.5">
                @foreach (['' => 'All', 'today' => 'Today', 'upcoming' => 'Upcoming', 'requested' => 'Requested', 'confirmed' => 'Confirmed', 'completed' => 'Completed'] as $value => $label)
                    @php $param = in_array($value, ['today', 'upcoming'], true) ? $value : 'status'; @endphp
                    <a href="{{ route('admin.appointments.index', $value === '' ? [] : [$param => $value === 'status' ? '' : $value]) }}"
                       @class([
                           'rounded-full px-3 py-1.5 text-xs font-medium',
                           'bg-ink-900 text-white' => request()->query($param, '') === $value || ($value === '' && ! request()->hasAny(['status', 'today', 'upcoming'])),
                           'bg-white text-ink-600 ring-1 ring-inset ring-ink-200 hover:bg-ink-50' => ! (request()->query($param, '') === $value || ($value === '' && ! request()->hasAny(['status', 'today', 'upcoming']))),
                       ])>{{ $label }}</a>
                @endforeach
            </div>

            @if ($appointments->isEmpty())
                <x-ui.empty title="No appointments" description="Visits booked from the website land here." icon="calendar" />
            @else
                <x-ui.card :padding="false">
                    <div class="px-5 py-1">
                        <x-ui.table :head="['When', 'Visitor', 'Type', 'With', 'Status', '']">
                            @foreach ($appointments as $appointment)
                                <tr>
                                    <td class="py-3 pl-0 pr-3">
                                        <p class="text-sm font-medium text-ink-900">{{ $appointment->starts_at->format('j M') }}</p>
                                        <p class="text-[11px] text-ink-500">{{ $appointment->starts_at->format('g:i A') }}–{{ $appointment->ends_at->format('g:i A') }}</p>
                                    </td>
                                    <td class="px-3 py-3">
                                        <p class="text-sm text-ink-800">{{ $appointment->name }}</p>
                                        <p class="text-[11px] text-ink-500">{{ $appointment->phone ?: $appointment->email }}</p>
                                    </td>
                                    <td class="px-3 py-3 text-xs text-ink-600">{{ \Illuminate\Support\Str::headline($appointment->type) }}</td>
                                    <td class="px-3 py-3 text-xs text-ink-600">{{ $appointment->staff?->name ?? 'Anyone' }}</td>
                                    <td class="px-3 py-3"><x-ui.status :status="$appointment->status" /></td>
                                    <td class="py-3 pl-3 pr-0 text-right">
                                        <x-ui.button href="{{ route('admin.appointments.edit', $appointment) }}" variant="ghost" icon="pencil">Edit</x-ui.button>
                                    </td>
                                </tr>
                            @endforeach
                        </x-ui.table>
                    </div>
                </x-ui.card>
                <x-ui.pagination :paginator="$appointments" />
            @endif
        </div>
    </div>
</x-layouts.admin>
