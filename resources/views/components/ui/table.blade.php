@props(['head' => []])
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-ink-150 text-sm">
        <thead>
            <tr class="text-left text-[11px] font-semibold uppercase tracking-wide text-ink-500">
                @foreach ($head as $column)
                    <th scope="col" class="whitespace-nowrap px-3 py-2 first:pl-0 last:pr-0">{{ $column }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-ink-100">
            {{ $slot }}
        </tbody>
    </table>
</div>
