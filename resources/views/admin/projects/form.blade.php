@php $isNew = ! $project->exists; @endphp
<x-layouts.admin :title="$isNew ? 'New project' : $project->name" :heading="$isNew ? 'New project' : $project->name"
                 :subheading="$isNew ? null : $project->reference"
                 :breadcrumbs="['Projects' => route('admin.projects.index')]">
    <form method="POST" action="{{ $isNew ? route('admin.projects.store') : route('admin.projects.update', $project) }}"
          class="grid gap-4 lg:grid-cols-3">
        @csrf
        @unless ($isNew) @method('PUT') @endunless

        <div class="space-y-4 lg:col-span-2">
            <x-ui.card title="Project">
                <div class="space-y-4">
                    <x-ui.field label="Name" name="name" required>
                        <input type="text" name="name" required value="{{ old('name', $project->name) }}"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-ui.field label="Customer" name="customer_id">
                            <select name="customer_id" class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                                <option value="">No customer</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" @selected(old('customer_id', $project->customer_id) == $customer->id)>{{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </x-ui.field>
                        <x-ui.field label="Project manager" name="manager_id">
                            <select name="manager_id" class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                                <option value="">Unassigned</option>
                                @foreach ($managers as $manager)
                                    <option value="{{ $manager->id }}" @selected(old('manager_id', $project->manager_id) == $manager->id)>{{ $manager->name }}</option>
                                @endforeach
                            </select>
                        </x-ui.field>
                        <x-ui.field label="Start date" name="start_date">
                            <input type="date" name="start_date" value="{{ old('start_date', $project->start_date?->format('Y-m-d')) }}"
                                   class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                        </x-ui.field>
                        <x-ui.field label="Due date" name="due_date">
                            <input type="date" name="due_date" value="{{ old('due_date', $project->due_date?->format('Y-m-d')) }}"
                                   class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                        </x-ui.field>
                        <x-ui.field label="Budget (Rs)" name="budget_minor">
                            <input type="number" name="budget_minor" min="0" step="0.01"
                                   value="{{ old('budget_minor', $project->budget_minor ? $project->budget_minor / 100 : '') }}"
                                   class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                        </x-ui.field>
                        <x-ui.field label="Site address" name="site_address">
                            <input type="text" name="site_address" value="{{ old('site_address', $project->site_address) }}"
                                   class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                        </x-ui.field>
                    </div>
                    <x-ui.field label="Notes" name="notes">
                        <textarea name="notes" rows="4"
                                  class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">{{ old('notes', $project->notes) }}</textarea>
                    </x-ui.field>
                </div>
            </x-ui.card>

            @unless ($isNew)
                <x-ui.card title="Stages" description="The workshop plan for this project." :padding="false">
                    <div class="px-5 py-1">
                        @forelse ($project->stages as $stage)
                            <div class="flex items-center justify-between gap-3 border-b border-ink-100 py-2.5 last:border-0">
                                <div>
                                    <p class="text-sm text-ink-800">{{ $stage->position }}. {{ $stage->name }}</p>
                                    <p class="text-[11px] text-ink-500">
                                        {{ $stage->started_on?->format('j M') ?? 'Not started' }}
                                        @if ($stage->completed_on) · done {{ $stage->completed_on->format('j M') }} @endif
                                    </p>
                                </div>
                                <x-ui.status :status="$stage->status" />
                            </div>
                        @empty
                            <p class="py-6 text-center text-sm text-ink-500">No stages defined. They are created with the project.</p>
                        @endforelse
                    </div>
                </x-ui.card>
            @endunless
        </div>

        <div class="space-y-4">
            <x-ui.card title="Status">
                <x-ui.field label="Stage" name="status" required>
                    <select name="status" class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                        @foreach (['planning', 'design', 'production', 'qc', 'delivery', 'installed', 'closed', 'on_hold'] as $status)
                            <option value="{{ $status }}" @selected(old('status', $project->status) === $status)>{{ \Illuminate\Support\Str::headline($status) }}</option>
                        @endforeach
                    </select>
                </x-ui.field>
            </x-ui.card>

            <div class="flex justify-end gap-2">
                <x-ui.button href="{{ route('admin.projects.index') }}" variant="secondary">Cancel</x-ui.button>
                <x-ui.button type="submit" icon="check">Save</x-ui.button>
            </div>

            @unless ($isNew)
                <x-ui.card title="Danger zone">
                    <x-ui.confirm-button :action="route('admin.projects.destroy', $project)" label="Delete project"
                                         message="Delete “{{ $project->name }}”?" />
                </x-ui.card>
            @endunless
        </div>
    </form>
</x-layouts.admin>
