@extends('layouts.public')

@section('title', 'Blog — Samutkarsh IAS Academy')

@section('content')
    <div class="mx-auto max-w-5xl px-4 py-12">
        <h1 class="text-2xl font-bold text-slate-900">Blog &amp; Articles</h1>

        {{-- Category tabs --}}
        @if ($categories->isNotEmpty())
            <div class="mt-4 flex flex-wrap gap-2">
                <a href="{{ route('public.blog.index') }}"
                   class="rounded-full px-3 py-1 text-sm {{ $active === '' ? 'bg-indigo-600 text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50' }}">All</a>
                @foreach ($categories as $category)
                    <a href="{{ route('public.blog.index', ['category' => $category->slug]) }}"
                       class="rounded-full px-3 py-1 text-sm {{ $active === $category->slug ? 'bg-indigo-600 text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50' }}">{{ $category->name }}</a>
                @endforeach
            </div>
        @endif

        @if ($posts->isEmpty())
            <p class="mt-10 text-center text-slate-500">No posts published yet.</p>
        @else
            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)
                    <a href="{{ route('public.blog.show', $post->slug) }}"
                       class="group flex flex-col rounded-xl overflow-hidden ring-1 ring-slate-200 bg-white hover:shadow-md transition">
                        @if ($post->featureImageUrl())
                            <div class="aspect-[16/9] bg-slate-100 overflow-hidden">
                                <img src="{{ $post->featureImageUrl() }}" alt="{{ $post->title }}" loading="lazy"
                                     class="h-full w-full object-cover group-hover:scale-105 transition duration-300">
                            </div>
                        @else
                            {{-- No feature image yet — branded placeholder keeps the card height consistent. --}}
                            <div class="aspect-[16/9] flex flex-col items-center justify-center gap-2 bg-gradient-to-br from-indigo-500 to-indigo-700 text-white/90">
                                <svg class="h-9 w-9" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                                </svg>
                                <span class="text-xs font-medium tracking-wide">Samutkarsh IAS Academy</span>
                            </div>
                        @endif
                        <div class="p-4 flex-1 flex flex-col">
                            @if ($post->category)
                                <span class="text-xs font-medium text-indigo-600">{{ $post->category->name }}</span>
                            @endif
                            <h2 class="mt-1 font-semibold text-slate-900 group-hover:text-indigo-700">{{ $post->title }}</h2>
                            @if ($post->excerpt)
                                <p class="mt-2 text-sm text-slate-600 line-clamp-3">{{ $post->excerpt }}</p>
                            @endif
                            <span class="mt-3 text-xs text-slate-400">{{ optional($post->published_at)->format('d M Y') }}</span>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-8">{{ $posts->links() }}</div>
        @endif
    </div>
@endsection
