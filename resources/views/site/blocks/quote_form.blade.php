@php $attrs = $block['attrs'] ?? []; @endphp
<section class="mx-auto max-w-3xl px-5 py-4" data-block="quote_form">
    <div class="rounded-2xl border border-ink-150 bg-white p-6 shadow-panel sm:p-8">
        <h2 class="font-display text-2xl font-semibold tracking-tight text-ink-900" data-bind="heading">{{ $attrs['heading'] ?? 'Request a quote' }}</h2>
        <p class="mt-1 text-sm text-ink-600" data-bind="body">{{ $attrs['body'] ?? '' }}</p>

        <form method="POST" action="{{ route('site.contact.store') }}" class="mt-6 space-y-4">
            @csrf
            <input type="hidden" name="source" value="quote_form">
            <input type="hidden" name="form" value="quote">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="qf-name" class="block text-xs font-medium text-ink-700">Name</label>
                    <input id="qf-name" name="name" required value="{{ old('name') }}"
                           class="mt-1 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                    @error('name', 'quote')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="qf-phone" class="block text-xs font-medium text-ink-700">Phone</label>
                    <input id="qf-phone" name="phone" required value="{{ old('phone') }}" placeholder="0300 0000000"
                           class="mt-1 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
                </div>
            </div>
            <div>
                <label for="qf-email" class="block text-xs font-medium text-ink-700">Email</label>
                <input id="qf-email" type="email" name="email" value="{{ old('email') }}"
                       class="mt-1 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">
            </div>
            <div>
                <label for="qf-message" class="block text-xs font-medium text-ink-700">What are you planning?</label>
                <textarea id="qf-message" name="message" rows="4"
                          class="mt-1 w-full rounded-lg border-ink-200 text-sm focus:border-brand-400 focus:ring-brand-400">{{ old('message') }}</textarea>
            </div>
            <button type="submit" class="w-full rounded-full bg-ink-900 px-6 py-3 text-sm font-semibold text-white hover:bg-ink-800 sm:w-auto">
                {{ $attrs['button_label'] ?? 'Send enquiry' }}
            </button>
        </form>
    </div>
</section>
