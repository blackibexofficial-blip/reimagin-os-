<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $product->seo_title ?: $product->name }} · {{ settings('site_name', 'Woodex') }}</title>
    @if ($product->seo_description)
        <meta name="description" content="{{ $product->seo_description }}">
    @endif
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=fraunces:500,600,700|inter:400,500,600" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-white text-ink-800 antialiased">
    <x-site.header />

    <main class="mx-auto max-w-6xl px-5 py-10">
        <nav class="text-xs text-ink-500" aria-label="Breadcrumb">
            <a href="{{ route('site.shop') }}" class="hover:text-brand-700">Shop</a>
            @if ($product->category)
                <span class="mx-1 text-ink-300">/</span>
                <a href="{{ route('site.categories.show', $product->category) }}" class="hover:text-brand-700">{{ $product->category->name }}</a>
            @endif
            <span class="mx-1 text-ink-300">/</span>
            <span class="text-ink-700">{{ $product->name }}</span>
        </nav>

        <div class="mt-6 grid gap-10 lg:grid-cols-2">
            <div>
                <div class="overflow-hidden rounded-2xl bg-ink-100">
                    @if ($product->featuredImage)
                        <img src="{{ $product->featuredImage->url() }}" alt="{{ $product->featuredImage->alt_text ?: $product->name }}"
                             class="aspect-[4/3] w-full object-cover">
                    @else
                        <div class="aspect-[4/3] w-full bg-gradient-to-br from-ink-100 to-ink-200"></div>
                    @endif
                </div>

                @if ($product->images->isNotEmpty())
                    <div class="mt-3 grid grid-cols-4 gap-3">
                        @foreach ($product->images->take(4) as $image)
                            <img src="{{ $image->url() }}" alt="" class="aspect-square w-full rounded-lg object-cover" loading="lazy">
                        @endforeach
                    </div>
                @endif
            </div>

            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-brand-700">{{ $product->category?->name ?? 'Woodex' }}</p>
                <h1 class="mt-2 font-display text-3xl font-semibold tracking-tight text-ink-900">{{ $product->name }}</h1>

                <p class="mt-3 text-2xl font-semibold text-ink-900">
                    {{ $product->is_quote_only ? 'Price on request' : money($product->price_minor, $product->currency) }}
                </p>

                <p class="mt-4 text-sm leading-relaxed text-ink-600">{{ $product->short_description }}</p>

                @if ($product->description)
                    <div class="prose prose-sm mt-4 max-w-none text-ink-600">{!! nl2br(e($product->description)) !!}</div>
                @endif

                <dl class="mt-6 grid grid-cols-2 gap-3 border-y border-ink-150 py-4 text-sm">
                    @foreach (['Material' => $product->material, 'Finish' => $product->finish, 'Dimensions' => $product->dimensions, 'Lead time' => $product->lead_time_days ? $product->lead_time_days.' days' : null] as $label => $value)
                        @if ($value)
                            <div>
                                <dt class="text-[11px] uppercase tracking-wide text-ink-400">{{ $label }}</dt>
                                <dd class="text-ink-800">{{ $value }}</dd>
                            </div>
                        @endif
                    @endforeach
                </dl>

                <div class="mt-6 space-y-3">
                    @if ($product->is_quote_only)
                        <a href="{{ route('site.contact') }}?piece={{ urlencode($product->name) }}"
                           class="flex w-full items-center justify-center gap-2 rounded-full bg-ink-900 px-6 py-3 text-sm font-semibold text-white hover:bg-ink-800">
                            Request a quotation
                        </a>
                    @else
                        <form method="POST" action="{{ route('site.cart.store') }}" class="flex items-center gap-3">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <label for="qty" class="text-xs font-medium text-ink-600">Quantity</label>
                            <input id="qty" type="number" name="quantity" value="1" min="1" max="20"
                                   class="w-20 rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                            <button type="submit"
                                    class="flex-1 rounded-full bg-ink-900 px-6 py-3 text-sm font-semibold text-white hover:bg-ink-800">
                                Add to basket
                            </button>
                        </form>
                    @endif

                    <a href="https://wa.me/{{ preg_replace('/\D+/', '', settings('whatsapp_number', '923004567890')) }}?text={{ urlencode('I would like to ask about the '.$product->name) }}"
                       target="_blank" rel="noopener"
                       class="flex w-full items-center justify-center gap-2 rounded-full border border-ink-200 px-6 py-3 text-sm font-semibold text-ink-800 hover:bg-ink-50">
                        <x-nav-icon name="chat" class="size-4" /> Ask a designer on WhatsApp
                    </a>
                </div>

                @if ($product->track_stock)
                    <p class="mt-4 text-xs {{ $product->stock_quantity > 0 ? 'text-emerald-700' : 'text-amber-700' }}">
                        {{ $product->stock_quantity > 0 ? $product->stock_quantity.' in stock, ready to dispatch' : 'Made to order — 4 to 6 weeks' }}
                    </p>
                @endif
            </div>
        </div>

        @if ($related->isNotEmpty())
            <section class="mt-16">
                <h2 class="font-display text-2xl font-semibold tracking-tight text-ink-900">More from the studio</h2>
                <div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $item)
                        <x-site.product-card :product="$item" />
                    @endforeach
                </div>
            </section>
        @endif
    </main>

    <x-site.footer />
</body>
</html>
