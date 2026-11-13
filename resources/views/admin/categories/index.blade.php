<x-layouts.admin title="Categories" heading="Categories" subheading="How the collection is grouped on the shop."
                :breadcrumbs="['Commerce' => route('admin.products.index')]">
    <x-slot:actions>
        <x-ui.button href="{{ route('admin.categories.create') }}" icon="plus">New category</x-ui.button>
    </x-slot:actions>

    @if ($categories->isEmpty())
        <x-ui.empty title="No categories yet" description="Group products so shoppers can browse by room." icon="tag">
            <x-slot:action><x-ui.button href="{{ route('admin.categories.create') }}" icon="plus">Add a category</x-ui.button></x-slot:action>
        </x-ui.empty>
    @else
        <x-ui.card :padding="false">
            <div class="px-5 py-1">
                <x-ui.table :head="['Category', 'Slug', 'Products', 'Order', 'Status', '']">
                    @foreach ($categories as $category)
                        <tr>
                            <td class="py-3 pl-0 pr-3">
                                <a href="{{ route('admin.categories.edit', $category) }}" class="font-medium text-ink-900 hover:text-brand-700">{{ $category->name }}</a>
                                @if ($category->description)
                                    <p class="line-clamp-1 text-xs text-ink-500">{{ $category->description }}</p>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-xs text-ink-500">/{{ $category->slug }}</td>
                            <td class="px-3 py-3 text-sm tabular-nums text-ink-700">{{ $category->products_count }}</td>
                            <td class="px-3 py-3 text-sm tabular-nums text-ink-500">{{ $category->position }}</td>
                            <td class="px-3 py-3">
                                <x-ui.badge :tone="$category->is_active ? 'success' : 'neutral'">{{ $category->is_active ? 'Active' : 'Hidden' }}</x-ui.badge>
                            </td>
                            <td class="py-3 pl-3 pr-0 text-right">
                                <x-ui.button href="{{ route('admin.categories.edit', $category) }}" variant="ghost" icon="pencil">Edit</x-ui.button>
                            </td>
                        </tr>
                    @endforeach
                </x-ui.table>
            </div>
        </x-ui.card>
        <x-ui.pagination :paginator="$categories" />
    @endif
</x-layouts.admin>
