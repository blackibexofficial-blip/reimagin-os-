@php
    $attrs = $block['attrs'] ?? [];
    $staff = \App\Models\User::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);
@endphp
<section class="mx-auto max-w-3xl px-5 py-4" data-block="appointment_form">
    <div class="rounded-2xl border border-ink-150 bg-white p-6 shadow-panel sm:p-8">
        <h2 class="font-display text-2xl font-semibold tracking-tight text-ink-900" data-bind="heading">{{ $attrs['heading'] ?? 'Book a visit' }}</h2>
        <p class="mt-1 text-sm text-ink-600" data-bind="body">{{ $attrs['body'] ?? '' }}</p>

        <form method="POST" action="{{ route('site.appointments.store') }}" class="mt-6 space-y-4">
            @csrf
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="af-name" class="block text-xs font-medium text-ink-700">Name</label>
                    <input id="af-name" name="name" required value="{{ old('name') }}"
                           class="mt-1 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                </div>
                <div>
                    <label for="af-phone" class="block text-xs font-medium text-ink-700">Phone</label>
                    <input id="af-phone" name="phone" required value="{{ old('phone') }}"
                           class="mt-1 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                </div>
                <div>
                    <label for="af-type" class="block text-xs font-medium text-ink-700">Visit type</label>
                    <select id="af-type" name="type" class="mt-1 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                        <option value="showroom">Showroom visit</option>
                        <option value="site_survey">Site survey</option>
                        <option value="consultation">Design consultation</option>
                    </select>
                </div>
                <div>
                    <label for="af-staff" class="block text-xs font-medium text-ink-700">With</label>
                    <select id="af-staff" name="staff_id" class="mt-1 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                        <option value="">Anyone available</option>
                        @foreach ($staff as $member)
                            <option value="{{ $member->id }}">{{ $member->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label for="af-when" class="block text-xs font-medium text-ink-700">Preferred date and time</label>
                    <input id="af-when" type="datetime-local" name="starts_at" required value="{{ old('starts_at') }}"
                           class="mt-1 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    @error('starts_at')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="af-notes" class="block text-xs font-medium text-ink-700">Anything we should know?</label>
                    <textarea id="af-notes" name="notes" rows="3"
                              class="mt-1 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">{{ old('notes') }}</textarea>
                </div>
            </div>
            <button type="submit" class="w-full rounded-full bg-brand-600 px-6 py-3 text-sm font-semibold text-white hover:bg-brand-700 sm:w-auto">
                Request this slot
            </button>
        </form>
    </div>
</section>
