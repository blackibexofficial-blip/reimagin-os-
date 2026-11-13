<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $post->seo_title ?: $post->title }} · {{ settings('site_name', 'Woodex') }}</title>
    @if ($post->seo_description)
        <meta name="description" content="{{ $post->seo_description }}">
    @endif
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=fraunces:500,600,700|inter:400,500,600" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-white text-ink-800 antialiased">
    <x-site.header />

    <main class="mx-auto max-w-3xl px-5 py-12">
        <a href="{{ route('site.blog') }}" class="text-xs font-medium text-brand-700 hover:text-brand-800">← The journal</a>

        <article class="mt-4">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-brand-700">
                {{ $post->category?->name ?? 'Journal' }} · {{ $post->published_at?->format('j F Y') }}
            </p>
            <h1 class="mt-2 font-display text-3xl font-semibold leading-tight tracking-tight text-ink-900 sm:text-4xl">{{ $post->title }}</h1>
            <p class="mt-2 text-sm text-ink-500">By {{ $post->author?->name ?? 'Woodex Studio' }} · {{ $post->views }} reads</p>

            @if ($post->featuredImage)
                <img src="{{ $post->featuredImage->url() }}" alt="" class="mt-6 aspect-[16/9] w-full rounded-xl object-cover">
            @endif

            <div class="prose prose-ink mt-6 max-w-none">
                {!! $post->body !!}
            </div>

            @if (is_array($post->tags) && $post->tags !== [])
                <div class="mt-8 flex flex-wrap gap-1.5">
                    @foreach ($post->tags as $tag)
                        <span class="rounded-full bg-ink-100 px-2.5 py-1 text-[11px] text-ink-600">{{ $tag }}</span>
                    @endforeach
                </div>
            @endif
        </article>

        @if ($more->isNotEmpty())
            <section class="mt-14 border-t border-ink-150 pt-8">
                <h2 class="font-display text-xl font-semibold text-ink-900">Keep reading</h2>
                <div class="mt-4 space-y-3">
                    @foreach ($more as $item)
                        <a href="{{ route('site.blog.show', $item) }}" class="flex items-center gap-3 rounded-lg p-2 hover:bg-ink-50">
                            <div class="size-14 shrink-0 overflow-hidden rounded-lg bg-ink-100">
                                @if ($item->featuredImage)
                                    <img src="{{ $item->featuredImage->url() }}" alt="" class="size-full object-cover">
                                @endif
                            </div>
                            <div>
                                <p class="text-sm font-medium text-ink-900">{{ $item->title }}</p>
                                <p class="text-xs text-ink-500">{{ $item->published_at?->format('j M Y') }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </main>

    <x-site.footer />
</body>
</html>
