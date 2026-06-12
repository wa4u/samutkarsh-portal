@extends('layouts.public')

@section('title', 'Gallery — Samutkarsh IAS Academy')

@section('content')
    <div class="mx-auto max-w-5xl px-4 py-12">
        <h1 class="text-2xl font-bold text-slate-900">Gallery</h1>
        <p class="mt-1 text-sm text-slate-600">Moments from across our centers.</p>

        {{-- Filters --}}
        <form method="GET" class="mt-6 grid gap-3 sm:grid-cols-4 rounded-xl bg-white p-4 ring-1 ring-slate-200">
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search albums…"
                   class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 sm:col-span-2">
            <select name="center" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">All centers</option>
                @foreach ($centers as $center)
                    <option value="{{ $center->id }}" @selected(($filters['center'] ?? '') == $center->id)>{{ $center->name }}</option>
                @endforeach
            </select>
            <select name="year" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">All years</option>
                @foreach ($years as $year)
                    <option value="{{ $year }}" @selected(($filters['year'] ?? '') == $year)>{{ $year }}</option>
                @endforeach
            </select>
            <div class="sm:col-span-4 flex gap-2">
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Filter</button>
                @if (array_filter($filters ?? []))
                    <a href="{{ route('public.gallery.index') }}" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">Clear</a>
                @endif
            </div>
        </form>

        @if ($galleries->isEmpty())
            <p class="mt-10 text-center text-slate-500">No albums match your filters.</p>
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
                                {{ $gallery->center?->name ?? 'Samutkarsh' }}@if ($gallery->year) · {{ $gallery->year }}@endif · {{ $gallery->items_count }} item{{ $gallery->items_count === 1 ? '' : 's' }}
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-8">{{ $galleries->links() }}</div>
        @endif
    </div>
@endsection
