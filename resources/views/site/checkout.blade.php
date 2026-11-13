<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Checkout · {{ settings('site_name', 'Woodex') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=fraunces:500,600,700|inter:400,500,600" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-white text-ink-800 antialiased">
    <x-site.header />

    <main class="mx-auto max-w-5xl px-5 py-12">
        <h1 class="font-display text-3xl font-semibold tracking-tight text-ink-900">Checkout</h1>
        <p class="mt-1 text-sm text-ink-600">No payment is taken online — the studio confirms your order and the delivery date by phone.</p>

        <div class="mt-8 grid gap-8 lg:grid-cols-5">
            <form method="POST" action="{{ route('site.checkout.store') }}" class="space-y-4 lg:col-span-3">
                @csrf

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="name" class="block text-xs font-medium text-ink-700">Full name</label>
                        <input id="name" name="name" required value="{{ old('name') }}"
                               class="mt-1 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                        @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="phone" class="block text-xs font-medium text-ink-700">Phone</label>
                        <input id="phone" name="phone" required value="{{ old('phone') }}" placeholder="0300 0000000"
                               class="mt-1 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                        @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-xs font-medium text-ink-700">Email</label>
                    <input id="email" type="email" name="email" required value="{{ old('email') }}"
                           class="mt-1 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="address_line_1" class="block text-xs font-medium text-ink-700">Delivery address</label>
                    <input id="address_line_1" name="address_line_1" required value="{{ old('address_line_1') }}"
                           class="mt-1 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    @error('address_line_1')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="city" class="block text-xs font-medium text-ink-700">City</label>
                    <input id="city" name="city" required value="{{ old('city', 'Lahore') }}"
                           class="mt-1 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    @error('city')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="notes" class="block text-xs font-medium text-ink-700">Notes for the workshop</label>
                    <textarea id="notes" name="notes" rows="3"
                              class="mt-1 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">{{ old('notes') }}</textarea>
                </div>

                <button type="submit" class="w-full rounded-full bg-ink-900 px-6 py-3 text-sm font-semibold text-white hover:bg-ink-800">
                    Place order
                </button>
                <p class="text-center text-[11px] text-ink-500">
                    By placing the order you agree to be contacted about measurements and delivery.
                </p>
            </form>

            <aside class="lg:col-span-2">
                <div class="rounded-xl border border-ink-150 bg-ink-50 p-5">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-500">Order summary</p>
                    <ul class="mt-3 space-y-2.5">
                        @foreach ($cart['lines'] as $line)
                            <li class="flex justify-between gap-3 text-sm">
                                <span class="text-ink-700">{{ $line['quantity'] }} × {{ $line['product']->name }}</span>
                                <span class="shrink-0 tabular-nums text-ink-900">{{ money($line['line_total_minor']) }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <div class="mt-4 flex justify-between border-t border-ink-200 pt-3 text-sm font-semibold">
                        <span>Subtotal</span>
                        <span>{{ money($cart['subtotal_minor']) }}</span>
                    </div>
                    <p class="mt-1 text-[11px] text-ink-500">Delivery quoted separately.</p>
                </div>
            </aside>
        </div>
    </main>

    <x-site.footer />
</body>
</html>
