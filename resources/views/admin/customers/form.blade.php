@php $isNew = ! $customer->exists; @endphp
<x-layouts.admin :title="$isNew ? 'New customer' : 'Edit customer'" :heading="$isNew ? 'New customer' : 'Edit customer'"
                 :breadcrumbs="['Customers' => route('admin.customers.index')]">
    <form method="POST" action="{{ $isNew ? route('admin.customers.store') : route('admin.customers.update', $customer) }}"
          class="grid gap-4 lg:grid-cols-3">
        @csrf
        @unless ($isNew) @method('PUT') @endunless

        <div class="space-y-4 lg:col-span-2">
            <x-ui.card title="Contact">
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.field label="Name" name="name" required>
                        <input type="text" name="name" required value="{{ old('name', $customer->name) }}"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                    <x-ui.field label="Company" name="company">
                        <input type="text" name="company" value="{{ old('company', $customer->company) }}"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                    <x-ui.field label="Email" name="email">
                        <input type="email" name="email" value="{{ old('email', $customer->email) }}"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                    <x-ui.field label="Phone" name="phone">
                        <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                </div>
            </x-ui.card>

            <x-ui.card title="Address">
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach (['address_line_1' => 'Address', 'address_line_2' => 'Address line 2', 'city' => 'City', 'state' => 'Province', 'postal_code' => 'Postal code', 'country' => 'Country'] as $field => $label)
                        <x-ui.field :label="$label" :name="$field">
                            <input type="text" name="{{ $field }}" value="{{ old($field, $customer->{$field}) }}"
                                   class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                        </x-ui.field>
                    @endforeach
                </div>
            </x-ui.card>
        </div>

        <div class="space-y-4">
            <x-ui.card title="Relationship">
                <div class="space-y-4">
                    <x-ui.field label="Account owner" name="owner_id">
                        <select name="owner_id" class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                            <option value="">Unassigned</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" @selected(old('owner_id', $customer->owner_id) == $user->id)>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </x-ui.field>
                    <x-ui.field label="Tags" name="tags" hint="Comma separated, e.g. kitchen, repeat.">
                        <input type="text" name="tags" value="{{ old('tags', is_array($customer->tags) ? implode(', ', $customer->tags) : '') }}"
                               class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    </x-ui.field>
                    <x-ui.field label="Notes" name="notes">
                        <textarea name="notes" rows="5"
                                  class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">{{ old('notes', $customer->notes) }}</textarea>
                    </x-ui.field>
                </div>
            </x-ui.card>

            <div class="flex justify-end gap-2">
                <x-ui.button href="{{ route('admin.customers.index') }}" variant="secondary">Cancel</x-ui.button>
                <x-ui.button type="submit" icon="check">{{ $isNew ? 'Create' : 'Save' }}</x-ui.button>
            </div>
        </div>
    </form>
</x-layouts.admin>
