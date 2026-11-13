<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Services · {{ settings('site_name', 'Woodex') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=fraunces:500,600,700|inter:400,500,600" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-white text-ink-800 antialiased">
    <x-site.header />

    <main class="mx-auto max-w-4xl px-5 py-12">
        <header class="max-w-2xl">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-brand-700">What we do</p>
            <h1 class="mt-2 font-display text-3xl font-semibold tracking-tight text-ink-900">From the first sketch to the last hinge</h1>
        </header>

        <div class="mt-10 space-y-4">
            @foreach ($services as $service)
                <article class="rounded-xl border border-ink-150 bg-white p-6">
                    <div class="flex items-start gap-4">
                        <span class="grid size-11 shrink-0 place-items-center rounded-full bg-brand-50 text-brand-700">
                            <x-nav-icon :name="$service->icon ?: 'wrench'" class="size-5" />
                        </span>
                        <div>
                            <h2 class="font-display text-xl font-semibold text-ink-900">{{ $service->title }}</h2>
                            <p class="mt-1 text-sm text-ink-600">{{ $service->excerpt }}</p>
                            @if ($service->body)
                                <div class="prose prose-sm mt-3 max-w-none text-ink-600">{!! $service->body !!}</div>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-10 rounded-2xl bg-ink-950 p-8 text-center text-white">
            <h2 class="font-display text-2xl font-semibold">Have a room in mind?</h2>
            <p class="mt-2 text-sm text-ink-300">Book a free consultation and we will bring samples to your site.</p>
            <a href="{{ route('site.contact') }}" class="mt-5 inline-flex rounded-full bg-brand-500 px-6 py-3 text-sm font-semibold text-ink-950 hover:bg-brand-400">
                Book a visit
            </a>
        </div>
    </main>

    <x-site.footer />
</body>
</html>
