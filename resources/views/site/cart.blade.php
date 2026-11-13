<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Basket · {{ settings('site_name', 'Woodex') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=fraunces:500,600,700|inter:400,500,600" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-white text-ink-800 antialiased">
    <x-site.header />

    <main class="mx-auto max-w-4xl px-5 py-12">
        <h1 class="font-display text-3xl font-semibold tracking-tight text-ink-900">Your basket</h1>

        @if (session('status'))
            <p class="mt-4 rounded-lg bg-emerald-50 px-4 py-2.5 text-sm text-emerald-800">{{ session('status') }}</p>
        @endif
        @if (session('error'))
            <p class="mt-4 rounded-lg bg-red-50 px-4 py-2.5 text-sm text-red-800">{{ session('error') }}</p>
        @endif

        @if ($cart['lines'] === [])
            <div class="mt-8 rounded-xl border border-dashed border-ink-200 py-16 text-center">
                <p class="font-medium text-ink-800">Your basket is empty</p>
                <p class="mt-1 text-sm text-ink-500">Pieces you add will appear here.</p>
                <a href="{{ route('site.shop') }}" class="mt-5 inline-flex rounded-full bg-ink-900 px-5 py-2.5 text-sm font-semibold text-white">Browse the collection</a>
            </div>
        @else
            <div class="mt-8 divide-y divide-ink-150 border-y border-ink-150">
                @foreach ($cart['lines'] as $line)
                    <div class="flex items-center gap-4 py-4">
                        <div class="size-20 shrink-0 overflow-hidden rounded-lg bg-ink-100">
                            @if ($line['product']->featuredImage)
                                <img src="{{ $line['product']->featuredImage->url() }}" alt="" class="size-full object-cover">
                            @endif
                        </div>

                        <div class="min-w-0 flex-1">
                            <a href="{{ route('site.products.show', $line['product']) }}" class="font-medium text-ink-900 hover:text-brand-800">
                                {{ $line['product']->name }}
                            </a>
                            <p class="text-xs text-ink-500">{{ money($line['unit_price_minor'], $line['product']->currency) }} each</p>

                            <div class="mt-2 flex items-center gap-2">
                                <form method="POST" action="{{ route('site.cart.update', $line['row']) }}" class="flex items-center gap-1.5">
                                    @csrf @method('PATCH')
                                    <input type="number" name="quantity" value="{{ $line['quantity'] }}" min="0" max="20"
                                           class="w-16 rounded-md border-ink-200 py-1 text-xs focus:border-brand-400 focus:ring-brand-400">
                                    <button type="submit" class="text-[11px] font-medium text-ink-600 underline hover:text-ink-900">Update</button>
                                </form>

                                <form method="POST" action="{{ route('site.cart.destroy', $line['row']) }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-[11px] font-medium text-red-600 underline hover:text-red-700">Remove</button>
                                </form>
                            </div>
                        </div>

                        <p class="shrink-0 font-semibold tabular-nums text-ink-900">{{ money($line['line_total_minor'], $line['product']->currency) }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-6 flex items-center justify-between">
                <p class="text-sm text-ink-500">{{ $cart['count'] }} {{ \Illuminate\Support\Str::plural('item', $cart['count']) }}</p>
                <p class="text-lg font-semibold text-ink-900">Subtotal {{ money($cart['subtotal_minor']) }}</p>
            </div>

            <p class="mt-2 text-right text-xs text-ink-500">Delivery is quoted after we confirm the survey.</p>

            <div class="mt-6 flex flex-wrap justify-end gap-3">
                <a href="{{ route('site.shop') }}" class="rounded-full border border-ink-200 px-5 py-2.5 text-sm font-semibold text-ink-800 hover:bg-ink-50">
                    Continue browsing
                </a>
                <a href="{{ route('site.checkout') }}" class="rounded-full bg-ink-900 px-6 py-2.5 text-sm font-semibold text-white hover:bg-ink-800">
                    Continue to checkout
                </a>
            </div>
        @endif
    </main>

    <x-site.footer />
</body>
</html>
