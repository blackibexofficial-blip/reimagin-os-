@php
    $navItems = \App\Models\NavigationItem::query()
        ->where('location', 'header')->whereNull('parent_id')->where('is_active', true)
        ->with('children')->orderBy('position')->get();
    $cartCount = collect(session('cart', []))->sum('quantity');
@endphp
<header x-data="{ open: false }" class="sticky top-0 z-40 border-b border-ink-150 bg-white/90 backdrop-blur">
    <div class="mx-auto flex max-w-6xl items-center gap-4 px-5 py-3.5">
        <a href="{{ route('site.home') }}" class="flex items-center gap-2">
            <x-logo class="size-6 text-brand-600" />
            <span class="font-display text-base font-semibold tracking-tight text-ink-900">{{ settings('site_name', 'Woodex') }}</span>
        </a>

        <nav class="ml-auto hidden items-center gap-1 md:flex" aria-label="Primary">
            @foreach ($navItems as $item)
                <a href="{{ $item->href() }}" class="rounded-full px-3 py-1.5 text-sm text-ink-600 transition hover:bg-ink-100 hover:text-ink-900">
                    {{ $item->label }}
                </a>
            @endforeach
        </nav>

        <div class="ml-auto flex items-center gap-2 md:ml-0">
            <a href="{{ route('site.shop') }}" class="hidden rounded-full px-3 py-1.5 text-sm text-ink-600 hover:bg-ink-100 sm:inline">Shop</a>
            <a href="{{ route('site.cart') }}" class="relative rounded-full p-2 text-ink-600 hover:bg-ink-100" aria-label="Cart">
                <x-nav-icon name="cart" class="size-5" />
                @if ($cartCount > 0)
                    <span class="absolute -right-0.5 -top-0.5 grid size-4 place-items-center rounded-full bg-brand-600 text-[9px] font-semibold text-white">{{ $cartCount }}</span>
                @endif
            </a>
            <a href="{{ route('site.contact') }}"
               class="hidden rounded-full bg-ink-900 px-4 py-2 text-sm font-semibold text-white hover:bg-ink-800 sm:inline">
                Book a visit
            </a>
            <button type="button" @click="open = ! open" class="rounded-full p-2 text-ink-600 md:hidden" aria-label="Menu">
                <x-nav-icon name="menu" class="size-5" />
            </button>
        </div>
    </div>

    <nav x-show="open" x-cloak class="border-t border-ink-150 bg-white px-5 py-3 md:hidden" aria-label="Mobile">
        @foreach ($navItems as $item)
            <a href="{{ $item->href() }}" class="block rounded-lg px-2 py-2 text-sm text-ink-700 hover:bg-ink-50">{{ $item->label }}</a>
        @endforeach
        <a href="{{ route('site.contact') }}" class="mt-2 block rounded-lg bg-ink-900 px-3 py-2 text-center text-sm font-semibold text-white">Book a visit</a>
    </nav>
</header>
