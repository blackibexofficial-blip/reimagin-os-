<x-layouts.guest title="Sign in">
    <h2 class="font-display text-2xl font-semibold tracking-tight text-white">Sign in</h2>
    <p class="mt-1 text-sm text-ink-400">Use your studio account to open the workspace.</p>

    @if (session('status'))
        <p class="mt-4 rounded-lg bg-emerald-500/10 px-3 py-2 text-xs text-emerald-300">{{ session('status') }}</p>
    @endif

    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <label for="email" class="block text-xs font-medium uppercase tracking-wide text-ink-400">Email</label>
            <input id="email" name="email" type="email" required autofocus autocomplete="username"
                   value="{{ old('email') }}"
                   class="mt-1.5 w-full rounded-lg border border-ink-700 bg-ink-900 px-3 py-2.5 text-sm text-white placeholder-ink-600 focus:border-brand-500 focus:ring-brand-500">
            @error('email')
                <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-xs font-medium uppercase tracking-wide text-ink-400">Password</label>
            <input id="password" name="password" type="password" required autocomplete="current-password"
                   class="mt-1.5 w-full rounded-lg border border-ink-700 bg-ink-900 px-3 py-2.5 text-sm text-white focus:border-brand-500 focus:ring-brand-500">
            @error('password')
                <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <label class="flex items-center gap-2 text-xs text-ink-400">
            <input type="checkbox" name="remember" value="1"
                   class="size-4 rounded border-ink-700 bg-ink-900 text-brand-600 focus:ring-brand-500">
            Keep me signed in
        </label>

        <button type="submit"
                class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-400">
            Sign in to Reimagin OS
        </button>
    </form>

    <p class="mt-8 text-[11px] leading-relaxed text-ink-600">
        Access is limited to active studio accounts. Contact an administrator if you have lost your credentials.
    </p>
</x-layouts.guest>
