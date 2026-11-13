@php $isNew = ! $lead->exists; @endphp
<x-layouts.admin :title="$isNew ? 'New lead' : 'Edit '.$lead->name" :heading="$isNew ? 'New lead' : 'Edit lead'"
                 :breadcrumbs="['Leads' => route('admin.leads.index')]">
    <form method="POST" action="{{ $isNew ? route('admin.leads.store') : route('admin.leads.update', $lead) }}"
          class="grid gap-4 lg:grid-cols-3">
        @csrf
        @unless ($isNew) @method('PUT') @endunless

        <div class="space-y-4 lg:col-span-2">
            <x-ui.card title="Contact">
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.field label="Name" name="name" required>
                        <input type="text" name="name" required value="{{ old('name', $lead->name) }}"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                    <x-ui.field label="Company" name="company">
                        <input type="text" name="company" value="{{ old('company', $lead->company) }}"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                    <x-ui.field label="Email" name="email">
                        <input type="email" name="email" value="{{ old('email', $lead->email) }}"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                    <x-ui.field label="Phone" name="phone">
                        <input type="text" name="phone" value="{{ old('phone', $lead->phone) }}"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                </div>
            </x-ui.card>

            <x-ui.card title="Enquiry">
                <div class="space-y-4">
                    <x-ui.field label="Message" name="message">
                        <textarea name="message" rows="4"
                                  class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">{{ old('message', $lead->message) }}</textarea>
                    </x-ui.field>
                    <x-ui.field label="Internal notes" name="notes">
                        <textarea name="notes" rows="3"
                                  class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">{{ old('notes', $lead->notes) }}</textarea>
                    </x-ui.field>
                </div>
            </x-ui.card>
        </div>

        <div class="space-y-4">
            <x-ui.card title="Qualification">
                <div class="space-y-4">
                    <x-ui.field label="Status" name="status">
                        <select name="status" class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                            @foreach (['new', 'contacted', 'qualified', 'proposal', 'won', 'lost'] as $status)
                                <option value="{{ $status }}" @selected(old('status', $lead->status) === $status)>{{ \Illuminate\Support\Str::headline($status) }}</option>
                            @endforeach
                        </select>
                    </x-ui.field>
                    <x-ui.field label="Priority" name="priority">
                        <select name="priority" class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                            @foreach (['low', 'normal', 'high', 'urgent'] as $priority)
                                <option value="{{ $priority }}" @selected(old('priority', $lead->priority) === $priority)>{{ \Illuminate\Support\Str::headline($priority) }}</option>
                            @endforeach
                        </select>
                    </x-ui.field>
                    <x-ui.field label="Source" name="source" required>
                        <input type="text" name="source" required value="{{ old('source', $lead->source) }}"
                               list="lead-sources"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                        <datalist id="lead-sources">
                            @foreach (['website', 'whatsapp', 'walk_in', 'instagram', 'referral', 'ai_agent', 'manual'] as $source)
                                <option value="{{ $source }}"></option>
                            @endforeach
                        </datalist>
                    </x-ui.field>
                    <x-ui.field label="Estimated value (Rs)" name="estimated_value">
                        <input type="number" name="estimated_value" min="0" step="0.01"
                               value="{{ old('estimated_value', $lead->estimated_value_minor ? $lead->estimated_value_minor / 100 : '') }}"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                    <x-ui.field label="Interest" name="product_id">
                        <select name="product_id" class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                            <option value="">Not specific</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" @selected(old('product_id', $lead->product_id) == $product->id)>{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </x-ui.field>
                    <x-ui.field label="Owner" name="assigned_to">
                        <select name="assigned_to" class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                            <option value="">Unassigned</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" @selected(old('assigned_to', $lead->assigned_to) == $user->id)>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </x-ui.field>
                </div>
            </x-ui.card>

            <div class="flex justify-end gap-2">
                <x-ui.button href="{{ route('admin.leads.index') }}" variant="secondary">Cancel</x-ui.button>
                <x-ui.button type="submit" icon="check">{{ $isNew ? 'Create lead' : 'Save' }}</x-ui.button>
            </div>
        </div>
    </form>
</x-layouts.admin>
