{{-- Latest blog --}}
@if (($latestPosts ?? collect())->isNotEmpty())
    <section class="mx-auto max-w-6xl px-4 py-20">
        <div class="flex items-end justify-between">
            <h2 class="text-3xl font-extrabold tracking-tight text-slate-900">Latest from our blog</h2>
            <a href="{{ route('public.blog.index') }}" class="text-sm font-semibold text-brand-600 hover:underline">View all &rarr;</a>
        </div>
        <div class="mt-10 grid gap-6 sm:grid-cols-3">
            @foreach ($latestPosts as $post)
                <a href="{{ route('public.blog.show', $post->slug) }}"
                   class="group flex flex-col rounded-2xl overflow-hidden ring-1 ring-slate-200 bg-white hover:shadow-md transition">
                    @if ($post->featureImageUrl())
                        <div class="aspect-[16/9] bg-slate-100 overflow-hidden">
                            <img src="{{ $post->featureImageUrl() }}" alt="{{ $post->title }}" loading="lazy"
                                 class="h-full w-full object-cover group-hover:scale-105 transition duration-300">
                        </div>
                    @else
                        <div class="aspect-[16/9] flex items-center justify-center bg-gradient-to-br from-brand-500 to-brand-700 text-white/90 text-sm font-semibold">Samutkarsh IAS</div>
                    @endif
                    <div class="p-5">
                        @if ($post->category)
                            <span class="text-xs font-semibold text-brand-600">{{ $post->category->name }}</span>
                        @endif
                        <h3 class="mt-1 font-bold text-slate-900 group-hover:text-brand-700">{{ $post->title }}</h3>
                        <span class="mt-2 block text-xs text-slate-400">{{ optional($post->published_at)->format('d M Y') }}</span>
                    </div>
                </a>
            @endforeach
        </div>
    </section>
@endif
