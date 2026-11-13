<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ isset($activeCategory) ? $activeCategory->name : 'Shop' }} · {{ settings('site_name', 'Woodex') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=fraunces:500,600,700|inter:400,500,600" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-white text-ink-800 antialiased">
    <x-site.header />

    <main class="mx-auto max-w-6xl px-5 py-12">
        <header class="max-w-2xl">
            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-brand-700">The collection</p>
            <h1 class="mt-2 font-display text-3xl font-semibold tracking-tight text-ink-900">
                {{ isset($activeCategory) ? $activeCategory->name : 'Furniture made to measure' }}
            </h1>
            <p class="mt-2 text-sm leading-relaxed text-ink-600">
                {{ $activeCategory->description ?? 'Every piece is built in our workshop from solid timber and finished by hand.' }}
            </p>
        </header>

        <div class="mt-8 flex flex-wrap items-center justify-between gap-3 border-y border-ink-150 py-3">
            <div class="flex flex-wrap items-center gap-1.5">
                <a href="{{ route('site.shop', request()->only('q')) }}"
                   @class([
                       'rounded-full px-3 py-1.5 text-xs font-medium',
                       'bg-ink-900 text-white' => ! request('category'),
                       'bg-white text-ink-600 ring-1 ring-inset ring-ink-200 hover:bg-ink-50' => (bool) request('category'),
                   ])>All</a>
                @foreach ($categories as $category)
                    <a href="{{ route('site.shop', array_filter(['category' => $category->id, 'q' => request('q')])) }}"
                       @class([
                           'rounded-full px-3 py-1.5 text-xs font-medium',
                           'bg-ink-900 text-white' => (int) request('category') === $category->id,
                           'bg-white text-ink-600 ring-1 ring-inset ring-ink-200 hover:bg-ink-50' => (int) request('category') !== $category->id,
                       ])>{{ $category->name }}</a>
                @endforeach
            </div>

            <form method="GET" class="flex items-center gap-2">
                @if (request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search pieces…"
                       class="rounded-lg border-ink-200 py-1.5 pl-3 pr-2 text-xs focus:border-brand-400 focus:ring-brand-400">
                <select name="sort" onchange="this.form.submit()"
                        class="rounded-lg border-ink-200 py-1.5 pl-2 pr-7 text-xs focus:border-brand-400 focus:ring-brand-400">
                    @foreach (['' => 'Featured', 'newest' => 'Newest', 'price_asc' => 'Price ↑', 'price_desc' => 'Price ↓'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('sort') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        @if ($products->isEmpty())
            <div class="mt-12 rounded-xl border border-dashed border-ink-200 py-16 text-center">
                <p class="font-medium text-ink-800">Nothing matches that search</p>
                <p class="mt-1 text-sm text-ink-500">Try another term, or <a href="{{ route('site.contact') }}" class="text-brand-700 underline">ask us to make it</a>.</p>
            </div>
        @else
            <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($products as $product)
                    <x-site.product-card :product="$product" />
                @endforeach
            </div>

            <div class="mt-10">{{ $products->links() }}</div>
        @endif
    </main>

    <x-site.footer />
</body>
</html>
