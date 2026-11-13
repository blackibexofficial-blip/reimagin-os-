@php
    $attrs = $block['attrs'] ?? [];
    $projects = \App\Models\ShowcaseProject::query()->where('is_published', true)
        ->orderByDesc('is_featured')->orderBy('position')->limit((int) ($attrs['limit'] ?? 6))->get();
@endphp
<section class="mx-auto max-w-6xl px-5 py-6" data-block="project_gallery">
    <h2 class="font-display text-2xl font-semibold tracking-tight text-ink-900" data-bind="heading">{{ $attrs['heading'] ?? '' }}</h2>
    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($projects as $project)
            <a href="{{ route('site.projects.show', $project) }}" class="group relative overflow-hidden rounded-xl bg-ink-900">
                @php $cover = is_array($project->gallery) ? (\App\Models\Media::find($project->gallery[0] ?? null)) : null; @endphp
                @if ($cover)
                    <img src="{{ $cover->url() }}" alt="{{ $project->title }}" class="aspect-[4/3] w-full object-cover opacity-90 transition group-hover:scale-105 group-hover:opacity-100">
                @else
                    <div class="aspect-[4/3] w-full bg-gradient-to-br from-ink-800 to-ink-950"></div>
                @endif
                <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-ink-950/90 to-transparent p-4">
                    <p class="text-sm font-semibold text-white">{{ $project->title }}</p>
                    <p class="text-[11px] text-ink-300">{{ $project->location }}{{ $project->year ? ' · '.$project->year : '' }}</p>
                </div>
            </a>
        @endforeach
    </div>
</section>
