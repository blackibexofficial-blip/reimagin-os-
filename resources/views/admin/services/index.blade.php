<x-layouts.admin title="Services" heading="Services" subheading="What the studio offers, shown on the services page."
                :breadcrumbs="['Website' => route('admin.services.index')]">
    <x-slot:actions>
        <x-ui.button href="{{ route('admin.services.create') }}" icon="plus">New service</x-ui.button>
    </x-slot:actions>

    @if ($services->isEmpty())
        <x-ui.empty title="No services yet" description="Describe what you offer — fitted wardrobes, kitchens, a design retainer." icon="wrench">
            <x-slot:action><x-ui.button href="{{ route('admin.services.create') }}" icon="plus">Add a service</x-ui.button></x-slot:action>
        </x-ui.empty>
    @else
        <x-ui.card :padding="false">
            <div class="px-5 py-1">
                <x-ui.table :head="['Service', 'Slug', 'Order', 'Status', '']">
                    @foreach ($services as $service)
                        <tr>
                            <td class="py-3 pl-0 pr-3">
                                <div class="flex items-center gap-2">
                                    <x-nav-icon :name="$service->icon ?: 'wrench'" class="size-4 text-brand-600" />
                                    <a href="{{ route('admin.services.edit', $service) }}" class="font-medium text-ink-900 hover:text-brand-700">{{ $service->title }}</a>
                                </div>
                                @if ($service->excerpt)<p class="line-clamp-1 pl-6 text-xs text-ink-500">{{ $service->excerpt }}</p>@endif
                            </td>
                            <td class="px-3 py-3 text-xs text-ink-500">{{ $service->slug }}</td>
                            <td class="px-3 py-3 text-sm tabular-nums text-ink-500">{{ $service->position }}</td>
                            <td class="px-3 py-3">
                                <x-ui.badge :tone="$service->is_published ? 'success' : 'neutral'">{{ $service->is_published ? 'Published' : 'Hidden' }}</x-ui.badge>
                            </td>
                            <td class="py-3 pl-3 pr-0 text-right">
                                <x-ui.button href="{{ route('admin.services.edit', $service) }}" variant="ghost" icon="pencil">Edit</x-ui.button>
                            </td>
                        </tr>
                    @endforeach
                </x-ui.table>
            </div>
        </x-ui.card>
        <x-ui.pagination :paginator="$services" />
    @endif
</x-layouts.admin>
