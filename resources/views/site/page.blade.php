<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $page->seo_title ?: $page->title }}</title>
    @if ($page->seo_description)
        <meta name="description" content="{{ $page->seo_description }}">
    @endif
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=fraunces:500,600,700|inter:400,500,600" rel="stylesheet">
    @vite(['resources/css/app.css'])
    @if (! empty($preview))
        <meta name="robots" content="noindex">
    @endif
</head>
<body class="bg-white text-ink-800 antialiased">
    @unless (! empty($preview))
        <x-site.header />
    @endunless

    <main>
        @forelse ($blocks as $block)
            @unless (! empty($block['hidden']))
                <x-site.block :block="$block" />
            @endunless
        @empty
            <div class="grid min-h-[50vh] place-items-center px-6 text-center">
                <div>
                    <p class="font-display text-2xl font-semibold text-ink-900">{{ $page->title }}</p>
                    <p class="mt-2 text-sm text-ink-500">This page has no blocks yet.</p>
                </div>
            </div>
        @endforelse
    </main>

    @unless (! empty($preview))
        <x-site.footer />
    @endunless
</body>
</html>
