@props(['title' => null, 'heading' => null, 'subheading' => null, 'breadcrumbs' => []])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title.' · Reimagin OS' : 'Reimagin OS' }}</title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='8' fill='%23141210'/%3E%3Cpath d='M8 24V8h7a5 5 0 0 1 0 10h-4M20 24V8h4.4a4.2 4.2 0 0 1 0 8.4H22L26 24' stroke='%23c87a42' stroke-width='2.2' fill='none' stroke-linecap='round'/%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=fraunces:500,600,700|inter:400,500,600,700" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-ink-50 text-ink-800 antialiased"
      x-data="{ sidebarOpen: false }">

<div class="flex min-h-full">
    {{-- Mobile backdrop --}}
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
         class="fixed inset-0 z-30 bg-ink-950/40 lg:hidden"></div>

    <aside class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col bg-ink-950 text-ink-200 transition-transform lg:translate-x-0"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
        <div class="flex items-center gap-2.5 px-5 py-5">
            <x-logo class="size-7 text-brand-500" />
            <div>
                <p class="font-display text-sm font-semibold tracking-tight text-white">Reimagin OS</p>
                <p class="text-[10px] uppercase tracking-[0.18em] text-ink-500">Woodex Studio</p>
            </div>
            <button type="button" @click="sidebarOpen = false" class="ml-auto text-ink-500 lg:hidden" aria-label="Close menu">
                <x-nav-icon name="chevron-right" class="size-5 rotate-180" />
            </button>
        </div>

        <nav class="flex-1 space-y-5 overflow-y-auto px-3 pb-6" aria-label="Main">
            @foreach (\App\Support\Navigation::for(auth()->user()) as $section)
                <div>
                    <p class="px-2 pb-1.5 text-[10px] font-semibold uppercase tracking-[0.16em] text-ink-600">{{ $section['label'] }}</p>
                    <ul class="space-y-0.5">
                        @foreach ($section['items'] as $item)
                            @php
                                $active = ! empty($item['match'])
                                    && request()->routeIs($item['match']);
                            @endphp
                            <li>
                                <a href="{{ route($item['route']) }}"
                                   @if (! empty($item['external'])) target="_blank" rel="noopener" @endif
                                   @class([
                                       'group flex items-center gap-2.5 rounded-lg px-2 py-1.5 text-[13px] transition',
                                       'bg-ink-800 text-white' => $active,
                                       'text-ink-300 hover:bg-ink-900 hover:text-white' => ! $active,
                                   ])
                                   @if ($active) aria-current="page" @endif>
                                    <x-nav-icon :name="$item['icon']" class="size-4 shrink-0 {{ $active ? 'text-brand-400' : 'text-ink-500 group-hover:text-ink-300' }}" />
                                    <span class="truncate">{{ $item['label'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </nav>

        <div class="border-t border-ink-800/70 p-3">
            <div class="flex items-center gap-2.5 rounded-lg px-2 py-1.5">
                @php $user = auth()->user(); @endphp
                <span class="grid size-8 shrink-0 place-items-center rounded-full bg-brand-600 text-xs font-semibold text-white">
                    {{ $user->initials() }}
                </span>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-[13px] font-medium text-white">{{ $user->name }}</p>
                    <p class="truncate text-[11px] text-ink-500">{{ $user->role->label() }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-ink-500 hover:text-white" title="Sign out" aria-label="Sign out">
                        <x-nav-icon name="external" class="size-4" />
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <div class="flex min-w-0 flex-1 flex-col lg:pl-64">
        <header class="sticky top-0 z-20 border-b border-ink-200 bg-white/90 backdrop-blur">
            <div class="flex items-center gap-3 px-4 py-3 sm:px-6">
                <button type="button" @click="sidebarOpen = true" class="text-ink-500 lg:hidden" aria-label="Open menu">
                    <x-nav-icon name="menu" class="size-5" />
                </button>

                <div class="min-w-0 flex-1">
                    @if ($breadcrumbs)
                        <nav class="flex items-center gap-1 text-[11px] text-ink-500" aria-label="Breadcrumb">
                            @foreach ($breadcrumbs as $label => $url)
                                <a href="{{ $url }}" class="hover:text-ink-800">{{ $label }}</a>
                                <span class="text-ink-300">/</span>
                            @endforeach
                        </nav>
                    @endif
                    <h1 class="truncate font-display text-lg font-semibold tracking-tight text-ink-900">
                        {{ $heading ?? $title ?? 'Dashboard' }}
                    </h1>
                    @if ($subheading)
                        <p class="truncate text-xs text-ink-500">{{ $subheading }}</p>
                    @endif
                </div>

                <div class="flex items-center gap-2">
                    @isset($actions){{ $actions }}@endisset
                </div>
            </div>

            @if (session('status'))
                <div class="border-t border-emerald-100 bg-emerald-50 px-4 py-2 text-xs font-medium text-emerald-800 sm:px-6" role="status">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="border-t border-red-100 bg-red-50 px-4 py-2 text-xs font-medium text-red-800 sm:px-6" role="alert">
                    {{ session('error') }}
                </div>
            @endif
        </header>

        <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
            {{ $slot }}
        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>
