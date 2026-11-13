@php
    $attrs = $block['attrs'] ?? [];
    $posts = \App\Models\BlogPost::query()->published()->with(['category', 'featuredImage'])
        ->latest('published_at')->limit((int) ($attrs['limit'] ?? 3))->get();
@endphp
<section class="mx-auto max-w-6xl px-5 py-6" data-block="blog_list">
    <div class="flex items-end justify-between">
        <h2 class="font-display text-2xl font-semibold tracking-tight text-ink-900" data-bind="heading">{{ $attrs['heading'] ?? '' }}</h2>
        <a href="{{ route('site.blog') }}" class="text-xs font-medium text-brand-700 hover:text-brand-800">All articles →</a>
    </div>
    <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($posts as $post)
            <a href="{{ route('site.blog.show', $post) }}" class="group">
                <div class="overflow-hidden rounded-xl bg-ink-100">
                    @if ($post->featuredImage)
                        <img src="{{ $post->featuredImage->url() }}" alt="" class="aspect-[16/10] w-full object-cover transition group-hover:scale-105">
                    @else
                        <div class="aspect-[16/10] w-full bg-gradient-to-br from-ink-100 to-ink-200"></div>
                    @endif
                </div>
                <p class="mt-3 text-[11px] font-medium uppercase tracking-wide text-brand-700">{{ $post->category?->name ?? 'Journal' }}</p>
                <h3 class="mt-1 font-medium leading-snug text-ink-900 group-hover:text-brand-800">{{ $post->title }}</h3>
                <p class="mt-1 line-clamp-2 text-sm text-ink-600">{{ $post->excerpt }}</p>
            </a>
        @endforeach
    </div>
</section>
