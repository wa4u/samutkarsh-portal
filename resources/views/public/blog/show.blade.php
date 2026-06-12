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
    </article>
@endsection
