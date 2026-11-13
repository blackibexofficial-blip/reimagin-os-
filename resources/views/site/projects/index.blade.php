<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Projects · {{ settings('site_name', 'Woodex') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=fraunces:500,600,700|inter:400,500,600" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-white text-ink-800 antialiased">
    <x-site.header />

    <main class="mx-auto max-w-6xl px-5 py-12">
        <header class="max-w-2xl">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-brand-700">The showroom</p>
            <h1 class="mt-2 font-display text-3xl font-semibold tracking-tight text-ink-900">Rooms we have made</h1>
            <p class="mt-2 text-sm text-ink-600">Completed commissions across Lahore, Islamabad and Karachi.</p>
        </header>

        @if ($projects->isEmpty())
            <p class="mt-10 rounded-xl border border-dashed border-ink-200 py-16 text-center text-sm text-ink-500">No projects published yet.</p>
        @else
            <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($projects as $project)
                    @php $cover = is_array($project->gallery) ? \App\Models\Media::find($project->gallery[0] ?? null) : null; @endphp
                    <a href="{{ route('site.projects.show', $project) }}" class="group relative overflow-hidden rounded-xl bg-ink-900">
                        @if ($cover)
                            <img src="{{ $cover->url() }}" alt="{{ $project->title }}" class="aspect-[4/3] w-full object-cover opacity-90 transition group-hover:scale-105">
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

            <div class="mt-10">{{ $projects->links() }}</div>
        @endif
    </main>

    <x-site.footer />
</body>
</html>
