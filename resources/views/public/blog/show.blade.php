@extends('layouts.public')

@section('title', $post->title . ' — Samutkarsh IAS Academy')

@section('content')
    <article class="mx-auto max-w-3xl px-4 py-12">
        <a href="{{ route('public.blog.index') }}" class="text-sm text-indigo-600 hover:underline">&larr; All posts</a>

        @if ($post->category)
            <p class="mt-4 text-sm font-medium text-indigo-600">{{ $post->category->name }}</p>
        @endif
        <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-900">{{ $post->title }}</h1>
        <p class="mt-2 text-sm text-slate-500">
            {{ optional($post->published_at)->format('d M Y') }}
            @if ($post->center) · {{ $post->center->name }} @endif
        </p>

        @if ($post->featureImageUrl())
            <img src="{{ $post->featureImageUrl() }}" alt="{{ $post->title }}"
                 class="mt-6 w-full rounded-xl ring-1 ring-slate-200">
        @endif

        {{-- Admin-authored rich text. --}}
        <div class="prose prose-slate mt-8 max-w-none">
            {!! $post->content !!}
        </div>

        @if (count($downloads = $post->downloads()))
            <div class="mt-10 rounded-xl border border-slate-200 bg-slate-50 p-5">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-700">Downloads</h2>
                <ul class="mt-3 space-y-2">
                    @foreach ($downloads as $file)
                        <li>
                            <a href="{{ $file['url'] }}" target="_blank" rel="noopener"
                               class="inline-flex items-center gap-2 text-indigo-600 hover:underline">
                                <svg class="h-5 w-5 shrink-0 text-rose-500" fill="none" viewBox="0 0 24 24"
                                     stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                </svg>
                                {{ $file['name'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </article>
@endsection
