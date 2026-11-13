<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contact · {{ settings('site_name', 'Woodex') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=fraunces:500,600,700|inter:400,500,600" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-white text-ink-800 antialiased">
    <x-site.header />

    <main class="mx-auto max-w-5xl px-5 py-12">
        <div class="grid gap-10 lg:grid-cols-2">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-brand-700">Say hello</p>
                <h1 class="mt-2 font-display text-3xl font-semibold tracking-tight text-ink-900">Tell us about the room</h1>
                <p class="mt-3 text-sm leading-relaxed text-ink-600">
                    Send the details and a designer will reply within one working day — or visit the showroom in Gulberg.
                </p>

                <dl class="mt-8 space-y-4 text-sm">
                    <div>
                        <dt class="text-[11px] uppercase tracking-wide text-ink-400">Showroom</dt>
                        <dd class="text-ink-800">{{ settings('address', '14-C Gulberg III, Lahore') }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] uppercase tracking-wide text-ink-400">Phone</dt>
                        <dd><a href="tel:{{ settings('phone', '+92 300 4567890') }}" class="text-ink-800 hover:text-brand-700">{{ settings('phone', '+92 300 4567890') }}</a></dd>
                    </div>
                    <div>
                        <dt class="text-[11px] uppercase tracking-wide text-ink-400">Email</dt>
                        <dd><a href="mailto:{{ settings('email', 'studio@woodex.pk') }}" class="text-ink-800 hover:text-brand-700">{{ settings('email', 'studio@woodex.pk') }}</a></dd>
                    </div>
                    <div>
                        <dt class="text-[11px] uppercase tracking-wide text-ink-400">Hours</dt>
                        <dd class="text-ink-800">Monday–Saturday, 11:00 – 19:00</dd>
                    </div>
                </dl>

                <a href="https://wa.me/{{ preg_replace('/\D+/', '', settings('whatsapp_number', '923004567890')) }}"
                   target="_blank" rel="noopener"
                   class="mt-8 inline-flex items-center gap-2 rounded-full bg-emerald-500 px-5 py-2.5 text-sm font-semibold text-ink-950 hover:bg-emerald-400">
                    <x-nav-icon name="chat" class="size-4" /> Chat on WhatsApp
                </a>
            </div>

            <div class="rounded-2xl border border-ink-150 bg-white p-6 shadow-panel">
                @if (session('status'))
                    <p class="mb-4 rounded-lg bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
                @endif
                @if (session('error'))
                    <p class="mb-4 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-800">{{ session('error') }}</p>
                @endif

                <form method="POST" action="{{ route('site.contact.store') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="form" value="contact">
                    <input type="hidden" name="source" value="contact_page">

                    @if ($piece)
                        <p class="rounded-lg bg-brand-50 px-3 py-2 text-xs text-brand-800">Enquiring about <strong>{{ $piece }}</strong></p>
                    @endif

                    <div>
                        <label for="c-name" class="block text-xs font-medium text-ink-700">Name</label>
                        <input id="c-name" name="name" required value="{{ old('name') }}"
                               class="mt-1 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                        @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="c-phone" class="block text-xs font-medium text-ink-700">Phone</label>
                            <input id="c-phone" name="phone" value="{{ old('phone') }}"
                                   class="mt-1 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                            @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="c-email" class="block text-xs font-medium text-ink-700">Email</label>
                            <input id="c-email" type="email" name="email" value="{{ old('email') }}"
                                   class="mt-1 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                            @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label for="c-message" class="block text-xs font-medium text-ink-700">Project details</label>
                        <textarea id="c-message" name="message" rows="5" placeholder="Room size, style, timeline…"
                                  class="mt-1 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">{{ old('message', $piece ? "I would like a quote for the {$piece}." : '') }}</textarea>
                    </div>

                    <button type="submit" class="w-full rounded-full bg-ink-900 px-6 py-3 text-sm font-semibold text-white hover:bg-ink-800">
                        Send enquiry
                    </button>
                </form>
            </div>
        </div>
    </main>

    <x-site.footer />
</body>
</html>
