<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>The journal · {{ settings('site_name', 'Woodex') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=fraunces:500,600,700|inter:400,500,600" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-white text-ink-800 antialiased">
    <x-site.header />

    <main class="mx-auto max-w-6xl px-5 py-12">
        <header class="max-w-2xl">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-brand-700">The journal</p>
            <h1 class="mt-2 font-display text-3xl font-semibold tracking-tight text-ink-900">Notes from the workshop</h1>
            <p class="mt-2 text-sm text-ink-600">Materials, joinery, and the rooms our furniture ends up in.</p>
        </header>

        @if ($posts->isEmpty())
            <p class="mt-10 rounded-xl border border-dashed border-ink-200 py-16 text-center text-sm text-ink-500">No articles published yet.</p>
        @else
            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)
                    <article>
                        <a href="{{ route('site.blog.show', $post) }}" class="group block">
                            <div class="overflow-hidden rounded-xl bg-ink-100">
                                @if ($post->featuredImage)
                                    <img src="{{ $post->featuredImage->url() }}" alt="" class="aspect-[16/10] w-full object-cover transition group-hover:scale-105">
                                @else
                                    <div class="aspect-[16/10] w-full bg-gradient-to-br from-ink-100 to-ink-200"></div>
                                @endif
                            </div>
                            <p class="mt-3 text-[11px] font-medium uppercase tracking-wide text-brand-700">
                                {{ $post->category?->name ?? 'Journal' }} · {{ $post->published_at?->format('j M Y') }}
                            </p>
                            <h2 class="mt-1 font-display text-lg font-semibold leading-snug text-ink-900 group-hover:text-brand-800">{{ $post->title }}</h2>
                            <p class="mt-1 line-clamp-2 text-sm text-ink-600">{{ $post->excerpt }}</p>
                        </a>
                    </article>
                @endforeach
            </div>

            <div class="mt-10">{{ $posts->links() }}</div>
        @endif
    </main>

    <x-site.footer />
</body>
</html>
