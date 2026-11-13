<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reimagin OS</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=fraunces:600|inter:400,600" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="grid min-h-screen place-items-center bg-ink-950 text-ink-100">
    <div class="max-w-md px-6 text-center">
        <x-logo class="mx-auto size-10 text-brand-500" />
        <h1 class="mt-5 font-display text-3xl font-semibold text-white">Reimagin OS</h1>
        <p class="mt-2 text-sm text-ink-400">The Woodex studio workspace. Storefront pages are being wired up next.</p>
        <a href="{{ route('login') }}" class="mt-6 inline-flex rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-500">
            Open the admin panel
        </a>
    </div>
</body>
</html>
