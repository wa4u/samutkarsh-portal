@extends('layouts.public')

@php
    $desc = $activity->excerpt(35);
    $shareUrl = url()->current();
@endphp

@section('title', $activity->title . ' — Samutkarsh IAS Academy')
@section('meta_description', $desc)
@section('og_title', $activity->title)
@if ($activity->shareImageUrl())
    @section('og_image', $activity->shareImageUrl())
@endif

@section('content')
    <section class="bg-gradient-to-br from-brand-600 to-brand-700 text-white">
        <div class="mx-auto max-w-3xl px-4 py-12">
            <a href="{{ route('public.activities') }}" class="inline-flex items-center gap-1 text-sm font-medium text-white/80 hover:text-white">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                All activities
            </a>
            <div class="mt-4 flex flex-wrap items-center gap-2 text-sm">
                <time class="font-semibold text-white/90">{{ $activity->date->format('d M Y') }}</time>
                @if ($activity->center)
                    <span class="inline-flex items-center rounded-full bg-white/15 px-2.5 py-0.5 text-xs font-medium ring-1 ring-white/20">{{ $activity->center }}</span>
                @endif
            </div>
            <h1 class="mt-2 text-3xl sm:text-4xl font-extrabold tracking-tight">{{ $activity->title }}</h1>
        </div>
    </section>

    <article class="mx-auto max-w-3xl px-4 py-10">
        <div class="prose max-w-none prose-strong:text-slate-800">{!! $activity->body !!}</div>

        {{-- Photos from this month's album --}}
        @if ($gallery && $gallery->items->isNotEmpty())
            <div class="mt-10">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-400">Photos from {{ $activity->date->format('F Y') }}</h2>
                <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3">
                    @foreach ($gallery->items->where('type', 'image')->take(6) as $item)
                        <a href="{{ route('public.gallery.show', $gallery->slug) }}" class="block overflow-hidden rounded-xl ring-1 ring-slate-200">
                            <img src="{{ $item->thumbUrl() }}" alt="" loading="lazy" class="aspect-square h-full w-full object-cover transition hover:scale-105">
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Share --}}
        <div class="mt-10 border-t border-slate-200 pt-6">
            <p class="text-sm font-semibold text-slate-500">Share this</p>
            <div class="mt-3 flex flex-wrap gap-2">
                <a target="_blank" rel="noopener" href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode($shareUrl) }}"
                   class="rounded-lg bg-[#0a66c2] px-4 py-2 text-sm font-semibold text-white hover:opacity-90">LinkedIn</a>
                <a target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}"
                   class="rounded-lg bg-[#1877f2] px-4 py-2 text-sm font-semibold text-white hover:opacity-90">Facebook</a>
                <a target="_blank" rel="noopener" href="https://twitter.com/intent/tweet?url={{ urlencode($shareUrl) }}&text={{ urlencode($activity->title) }}"
                   class="rounded-lg bg-black px-4 py-2 text-sm font-semibold text-white hover:opacity-90">X</a>
                <a target="_blank" rel="noopener" href="https://api.whatsapp.com/send?text={{ urlencode($activity->title . ' ' . $shareUrl) }}"
                   class="rounded-lg bg-[#25d366] px-4 py-2 text-sm font-semibold text-white hover:opacity-90">WhatsApp</a>
            </div>
        </div>
    </article>
@endsection
