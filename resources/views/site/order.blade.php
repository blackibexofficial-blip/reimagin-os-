<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order {{ $order->reference }} · {{ settings('site_name', 'Woodex') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=fraunces:500,600,700|inter:400,500,600" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-white text-ink-800 antialiased">
    <x-site.header />

    <main class="mx-auto max-w-3xl px-5 py-12">
        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-brand-700">Order placed</p>
        <h1 class="mt-2 font-display text-3xl font-semibold tracking-tight text-ink-900">Thank you, {{ $order->customer?->name }}</h1>
        <p class="mt-2 text-sm text-ink-600">
            Reference <span class="font-mono font-medium text-ink-900">{{ $order->reference }}</span> ·
            placed {{ $order->placed_at?->format('j F Y') }}. We will call {{ $order->phone }} to confirm.
        </p>

        <div class="mt-8 rounded-xl border border-ink-150">
            <div class="divide-y divide-ink-100 px-5">
                @foreach ($order->items as $item)
                    <div class="flex justify-between gap-4 py-3 text-sm">
                        <span class="text-ink-700">{{ $item->quantity }} × {{ $item->name }}</span>
                        <span class="tabular-nums text-ink-900">{{ money($item->line_total_minor, $order->currency) }}</span>
                    </div>
                @endforeach
            </div>
            <div class="border-t border-ink-150 bg-ink-50 px-5 py-3">
                <div class="flex justify-between text-sm font-semibold">
                    <span>Total</span>
                    <span>{{ money($order->total_minor, $order->currency) }}</span>
                </div>
                <div class="mt-1 flex justify-between text-xs text-ink-500">
                    <span>Paid so far</span>
                    <span>{{ money($order->paid_minor, $order->currency) }}</span>
                </div>
            </div>
        </div>

        <div class="mt-6 flex flex-wrap gap-3">
            <a href="{{ route('site.shop') }}" class="rounded-full border border-ink-200 px-5 py-2.5 text-sm font-semibold text-ink-800 hover:bg-ink-50">
                Continue browsing
            </a>
            <a href="https://wa.me/{{ preg_replace('/\D+/', '', settings('whatsapp_number', '923004567890')) }}?text={{ urlencode('Hello, I have a question about order '.$order->reference) }}"
               target="_blank" rel="noopener"
               class="rounded-full bg-emerald-500 px-5 py-2.5 text-sm font-semibold text-ink-950 hover:bg-emerald-400">
                Ask about this order
            </a>
        </div>
    </main>

    <x-site.footer />
</body>
</html>
