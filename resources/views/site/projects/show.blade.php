<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $project->title }} · {{ settings('site_name', 'Woodex') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=fraunces:500,600,700|inter:400,500,600" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-white text-ink-800 antialiased">
    <x-site.header />

    <main class="mx-auto max-w-4xl px-5 py-12">
        <a href="{{ route('site.projects') }}" class="text-xs font-medium text-brand-700 hover:text-brand-800">← All projects</a>

        <header class="mt-4">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-brand-700">{{ $project->category ?: 'Commission' }}</p>
            <h1 class="mt-2 font-display text-3xl font-semibold tracking-tight text-ink-900 sm:text-4xl">{{ $project->title }}</h1>
            <p class="mt-2 text-sm text-ink-500">
                {{ $project->location }}
                @if ($project->client) · for {{ $project->client }} @endif
                @if ($project->year) · {{ $project->year }} @endif
            </p>
            @if ($project->summary)
                <p class="mt-4 text-base leading-relaxed text-ink-700">{{ $project->summary }}</p>
            @endif
        </header>

        @if ($gallery->isNotEmpty())
            <div class="mt-8 grid gap-3 sm:grid-cols-2">
                @foreach ($gallery as $index => $image)
                    <img src="{{ $image->url() }}" alt="{{ $image->alt_text ?: $project->title }}"
                         class="w-full rounded-xl object-cover {{ $index === 0 ? 'sm:col-span-2 aspect-[16/9]' : 'aspect-[4/3]' }}">
                @endforeach
            </div>
        @endif

        @if ($project->description)
            <div class="prose prose-ink mt-8 max-w-none">{!! $project->description !!}</div>
        @endif

        @if ($more->isNotEmpty())
            <section class="mt-14 border-t border-ink-150 pt-8">
                <h2 class="font-display text-xl font-semibold text-ink-900">More projects</h2>
                <div class="mt-4 grid gap-3 sm:grid-cols-3">
                    @foreach ($more as $item)
                        <a href="{{ route('site.projects.show', $item) }}" class="rounded-lg border border-ink-150 p-3 hover:border-brand-300">
                            <p class="text-sm font-medium text-ink-900">{{ $item->title }}</p>
                            <p class="text-xs text-ink-500">{{ $item->location }}</p>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </main>

    <x-site.footer />
</body>
</html>
