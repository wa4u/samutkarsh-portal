@extends('layouts.public')

@section('title', 'Gallery — Samutkarsh IAS Academy')

@section('content')
    <div class="mx-auto max-w-5xl px-4 py-12">
        <h1 class="text-2xl font-bold text-slate-900">Gallery</h1>
        <p class="mt-1 text-sm text-slate-600">Moments from across our centers.</p>

        @if ($galleries->isEmpty())
            <p class="mt-10 text-center text-slate-500">No albums published yet. Please check back soon.</p>
        @else
            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($galleries as $gallery)
                    <a href="{{ route('public.gallery.show', $gallery->slug) }}"
                       class="group rounded-xl overflow-hidden ring-1 ring-slate-200 bg-white hover:shadow-md transition">
                        <div class="aspect-[4/3] bg-slate-100 overflow-hidden">
                            @if ($gallery->coverUrl())
                                <img src="{{ $gallery->coverUrl() }}" alt="{{ $gallery->title }}" loading="lazy"
                                     class="h-full w-full object-cover group-hover:scale-105 transition duration-300">
                            @endif
                        </div>
                        <div class="p-4">
                            <h2 class="font-semibold text-slate-900">{{ $gallery->title }}</h2>
                            <p class="mt-1 text-xs text-slate-500">
                                {{ $gallery->center?->name ?? 'Samutkarsh' }} · {{ $gallery->items_count }} item{{ $gallery->items_count === 1 ? '' : 's' }}
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-8">{{ $galleries->links() }}</div>
        @endif
    </div>
@endsection
