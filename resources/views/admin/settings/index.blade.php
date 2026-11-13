<x-layouts.admin title="Settings" heading="Settings" subheading="Brand, contact details and integrations."
                :breadcrumbs="['System' => route('admin.settings.index')]">
    <div x-data="{ tab: '{{ array_key_first($groups) }}' }">
        <div class="mb-4 flex flex-wrap gap-1.5">
            @foreach ($groups as $key => $group)
                <button type="button" @click="tab = '{{ $key }}'"
                        :class="tab === '{{ $key }}' ? 'bg-ink-900 text-white' : 'bg-white text-ink-600 ring-1 ring-inset ring-ink-200 hover:bg-ink-50'"
                        class="rounded-full px-3 py-1.5 text-xs font-medium transition">{{ $group['label'] }}</button>
            @endforeach
        </div>

        @foreach ($groups as $key => $group)
            <div x-show="tab === '{{ $key }}'" x-cloak>
                <x-ui.card :title="$group['label'].' settings'">
                    <form method="POST" action="{{ route('admin.settings.update') }}" class="max-w-xl space-y-4">
                        @csrf @method('PUT')
                        <input type="hidden" name="group" value="{{ $key }}">

                        @foreach ($group['fields'] as $field)
                            <x-ui.field :label="$field['label']" :name="$field['name']" :hint="$field['hint']">
                                @if ($field['type'] === 'textarea')
                                    <textarea name="{{ $field['name'] }}" rows="3"
                                              class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">{{ $field['value'] }}</textarea>
                                @else
                                    <input type="text" name="{{ $field['name'] }}" value="{{ $field['value'] }}"
                                           class="mt-1.5 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                                @endif
                            </x-ui.field>
                        @endforeach

                        <x-ui.button type="submit" icon="check">Save {{ strtolower($group['label']) }}</x-ui.button>
                    </form>
                </x-ui.card>
            </div>
        @endforeach
    </div>
</x-layouts.admin>
