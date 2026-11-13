@php
    $footerItems = \App\Models\NavigationItem::query()
        ->where('location', 'footer')->where('is_active', true)->orderBy('position')->get();
@endphp
<footer class="mt-16 border-t border-ink-150 bg-ink-50">
    <div class="mx-auto grid max-w-6xl gap-8 px-5 py-12 sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <div class="flex items-center gap-2">
                <x-logo class="size-6 text-brand-600" />
                <span class="font-display text-base font-semibold tracking-tight text-ink-900">{{ settings('site_name', 'Woodex') }}</span>
            </div>
            <p class="mt-3 text-xs leading-relaxed text-ink-500">
                {{ settings('site_tagline', 'Furniture, designed once — then made to measure.') }}
            </p>
        </div>

        <div>
            <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-500">Explore</p>
            <ul class="mt-3 space-y-1.5 text-sm text-ink-600">
                <li><a href="{{ route('site.shop') }}" class="hover:text-brand-700">Shop</a></li>
                <li><a href="{{ route('site.projects') }}" class="hover:text-brand-700">Projects</a></li>
                <li><a href="{{ route('site.services') }}" class="hover:text-brand-700">Services</a></li>
                <li><a href="{{ route('site.blog') }}" class="hover:text-brand-700">Journal</a></li>
            </ul>
        </div>

        <div>
            <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-500">More</p>
            <ul class="mt-3 space-y-1.5 text-sm text-ink-600">
                @foreach ($footerItems as $item)
                    <li><a href="{{ $item->href() }}" class="hover:text-brand-700">{{ $item->label }}</a></li>
                @endforeach
                <li><a href="{{ route('site.contact') }}" class="hover:text-brand-700">Contact</a></li>
            </ul>
        </div>

        <div>
            <p class="text-[11px] font-semibold uppercase tracking-wide text-ink-500">Studio</p>
            <ul class="mt-3 space-y-1.5 text-sm text-ink-600">
                <li>{{ settings('address', '14-C Gulberg III, Lahore') }}</li>
                <li><a href="tel:{{ settings('phone', '+92 300 4567890') }}" class="hover:text-brand-700">{{ settings('phone', '+92 300 4567890') }}</a></li>
                <li><a href="mailto:{{ settings('email', 'studio@woodex.pk') }}" class="hover:text-brand-700">{{ settings('email', 'studio@woodex.pk') }}</a></li>
            </ul>
        </div>
    </div>
    <div class="border-t border-ink-150 px-5 py-4">
        <p class="mx-auto max-w-6xl text-[11px] text-ink-400">
            © {{ date('Y') }} {{ settings('site_name', 'Woodex') }}. Built and run on Reimagin OS.
        </p>
    </div>
</footer>
