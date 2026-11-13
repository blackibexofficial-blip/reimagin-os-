@props(['title' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? $title.' · Reimagin OS' : 'Sign in · Reimagin OS' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=fraunces:500,600,700|inter:400,500,600,700" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-ink-950 text-ink-100">
<div class="flex min-h-full flex-col lg:flex-row">
    <aside class="relative hidden flex-1 flex-col justify-between overflow-hidden p-12 lg:flex">
        <div class="absolute inset-0 opacity-[0.07]"
             style="background-image: radial-gradient(circle at 20% 20%, #c87a42 0, transparent 45%), radial-gradient(circle at 80% 70%, #8c4c28 0, transparent 50%);"></div>
        <div class="relative flex items-center gap-3">
            <x-logo class="size-8 text-brand-500" />
            <div>
                <p class="font-display text-lg font-semibold tracking-tight text-white">Reimagin OS</p>
                <p class="text-[10px] uppercase tracking-[0.2em] text-ink-500">Woodex Studio · Lahore</p>
            </div>
        </div>
        <div class="relative max-w-md">
            <p class="font-display text-3xl leading-snug text-white">
                Furniture, designed once —<br>then made to measure.
            </p>
            <p class="mt-4 text-sm leading-relaxed text-ink-400">
                One workspace for the showroom, the workshop floor and the sales desk:
                leads, quotations, production, inventory and the live website.
            </p>
        </div>
        <p class="relative text-[11px] text-ink-600">© {{ date('Y') }} Woodex. All rights reserved.</p>
    </aside>

    <main class="flex flex-1 items-center justify-center px-6 py-12">
        <div class="w-full max-w-sm">
            <div class="mb-8 flex items-center gap-2.5 lg:hidden">
                <x-logo class="size-7 text-brand-500" />
                <p class="font-display text-base font-semibold text-white">Reimagin OS</p>
            </div>
            {{ $slot }}
        </div>
    </main>
</div>
</body>
</html>
