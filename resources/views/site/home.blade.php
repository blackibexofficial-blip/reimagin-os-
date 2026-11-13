<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $page?->seo_title ?: ($page?->title ?? settings('site_name', 'Woodex')) }}</title>
    @if ($page?->seo_description)
        <meta name="description" content="{{ $page->seo_description }}">
    @endif
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=fraunces:500,600,700|inter:400,500,600" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-white text-ink-800 antialiased">
    <x-site.header />

    <main>
        @if ($blocks !== [])
            @foreach ($blocks as $block)
                @unless (! empty($block['hidden']))
                    <x-site.block :block="$block" />
                @endunless
            @endforeach
        @else
            <section class="bg-ink-950 text-white">
                <div class="mx-auto max-w-6xl px-5 py-24">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-brand-400">{{ settings('site_name', 'Woodex') }} Studio</p>
                    <h1 class="mt-3 max-w-2xl font-display text-4xl font-semibold leading-tight tracking-tight sm:text-5xl">
                        Furniture, designed once — then made to measure.
                    </h1>
                    <p class="mt-4 max-w-xl text-base text-ink-300">
                        Fitted wardrobes, modular kitchens and custom pieces, made in our Lahore workshop.
                    </p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="{{ route('site.shop') }}" class="rounded-full bg-brand-500 px-6 py-3 text-sm font-semibold text-ink-950 hover:bg-brand-400">Browse the collection</a>
                        <a href="{{ route('site.contact') }}" class="rounded-full border border-white/25 px-6 py-3 text-sm font-semibold text-white hover:bg-white/10">Book a showroom visit</a>
                    </div>
                </div>
            </section>
        @endif

        @if ($featured->isNotEmpty())
            <section class="mx-auto max-w-6xl px-5 py-16">
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <h2 class="font-display text-2xl font-semibold tracking-tight text-ink-900">Featured pieces</h2>
                        <p class="mt-1 text-sm text-ink-500">Made to order, delivered across Pakistan.</p>
                    </div>
                    <a href="{{ route('site.shop') }}" class="text-sm font-medium text-brand-700 hover:text-brand-800">Shop all →</a>
                </div>
                <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($featured as $product)
                        <x-site.product-card :product="$product" />
                    @endforeach
                </div>
            </section>
        @endif
    </main>

    <x-site.footer />
</body>
</html>
