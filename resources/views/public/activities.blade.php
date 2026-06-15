@extends('layouts.public')

@section('title', 'Activities & sessions — Samutkarsh IAS Academy')
@section('meta_description', 'Week-by-week reports of Samutkarsh sessions, events and field activities across our centres.')

@section('content')
    {{-- Hero --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-brand-600 via-brand-600 to-brand-700 text-white">
        <div class="absolute inset-0 opacity-10" style="background-image:radial-gradient(circle at 20% 20%, #fff 1px, transparent 1px); background-size:22px 22px;"></div>
        <div class="relative mx-auto max-w-5xl px-4 py-14 sm:py-16">
            <p class="text-sm font-semibold uppercase tracking-widest text-white/70">Our journey, week by week</p>
            <h1 class="mt-2 text-3xl sm:text-4xl font-extrabold tracking-tight">Activities &amp; Sessions</h1>
            <p class="mt-3 max-w-2xl text-white/90">Reports from our sessions, events and field visits across every Samutkarsh centre — straight from our mentors and coordinators.</p>
        </div>
    </section>

    <div class="mx-auto max-w-5xl px-4 py-10 sm:py-12">

        {{-- Search --}}
        <form method="GET" action="{{ route('public.activities') }}" class="mb-6">
            @if ($activeCenter !== '')<input type="hidden" name="center" value="{{ $activeCenter }}">@endif
            @if ($activeYear !== '')<input type="hidden" name="year" value="{{ $activeYear }}">@endif
            <div class="relative">
                <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                <input type="search" name="q" value="{{ $searchTerm }}" placeholder="Search activities — topic, speaker, place…"
                       class="w-full rounded-full border-0 bg-white py-3 pl-12 pr-28 text-slate-700 ring-1 ring-slate-200 placeholder:text-slate-400 focus:ring-2 focus:ring-brand-500">
                <button type="submit" class="absolute right-1.5 top-1/2 -translate-y-1/2 rounded-full bg-brand-600 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-700">Search</button>
            </div>
            @if ($searchTerm !== '')
                <p class="mt-2 px-2 text-sm text-slate-500">Showing results for “{{ $searchTerm }}” —
                    <a href="{{ request()->fullUrlWithQuery(['q' => null, 'page' => null]) }}" class="font-medium text-brand-600 hover:underline">clear</a>
                </p>
            @endif
        </form>

        {{-- Filters --}}
        @if ($centers->isNotEmpty() || $years->isNotEmpty())
            <div class="mb-8 space-y-3">
                @if ($centers->isNotEmpty())
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-semibold uppercase tracking-wide text-slate-400">Centre</span>
                        <a href="{{ request()->fullUrlWithQuery(['center' => null, 'page' => null]) }}"
                           class="rounded-full px-3 py-1 text-sm font-medium transition {{ $activeCenter === '' ? 'bg-brand-600 text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-brand-50' }}">All</a>
                        @foreach ($centers as $c)
                            <a href="{{ request()->fullUrlWithQuery(['center' => $c, 'page' => null]) }}"
                               class="rounded-full px-3 py-1 text-sm font-medium transition {{ $activeCenter === $c ? 'bg-brand-600 text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-brand-50' }}">{{ $c }}</a>
                        @endforeach
                    </div>
                @endif
                @if ($years->isNotEmpty())
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-semibold uppercase tracking-wide text-slate-400">Year</span>
                        <a href="{{ request()->fullUrlWithQuery(['year' => null, 'page' => null]) }}"
                           class="rounded-full px-3 py-1 text-sm font-medium transition {{ $activeYear === '' ? 'bg-ink-600 text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-100' }}">All</a>
                        @foreach ($years as $y)
                            <a href="{{ request()->fullUrlWithQuery(['year' => $y, 'page' => null]) }}"
                               class="rounded-full px-3 py-1 text-sm font-medium transition {{ $activeYear === (string) $y ? 'bg-ink-600 text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-100' }}">{{ $y }}</a>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        @if ($activities->isEmpty())
            <div class="rounded-2xl bg-white py-16 text-center ring-1 ring-slate-200">
                <svg class="mx-auto h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                <p class="mt-3 text-slate-500">No activities found{{ $searchTerm !== '' ? ' for “'.$searchTerm.'”' : '' }}.</p>
            </div>
        @else
            <div class="space-y-5">
                @foreach ($activities as $a)
                    @php
                        $gallery = $galleries['samutkarsh-' . $a->date->format('Y-m')] ?? null;
                        $thumbs = $gallery ? $gallery->items->take(4) : collect();
                        $long = mb_strlen(strip_tags($a->body)) > 600;
                    @endphp
                    <article class="group flex gap-4 sm:gap-6 rounded-2xl bg-white p-5 sm:p-6 ring-1 ring-slate-200 shadow-sm transition hover:shadow-md hover:ring-brand-200">
                        {{-- Date block --}}
                        <div class="hidden sm:flex h-16 w-16 shrink-0 flex-col items-center justify-center rounded-xl bg-brand-50 text-brand-700 ring-1 ring-brand-100">
                            <span class="text-2xl font-extrabold leading-none">{{ $a->date->format('d') }}</span>
                            <span class="text-[11px] font-bold uppercase tracking-wide">{{ $a->date->format('M') }}</span>
                            <span class="text-[10px] text-brand-400">{{ $a->date->format('Y') }}</span>
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-sm font-semibold text-brand-700 sm:hidden">{{ $a->date->format('d M Y') }}</span>
                                @if ($a->center)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">
                                        <svg class="h-3 w-3 text-brand-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9.69 18.933.97 10.213a1.5 1.5 0 0 1 0-2.121l7.07-7.071a1.5 1.5 0 0 1 2.122 0l7.07 7.07a1.5 1.5 0 0 1 0 2.122l-7.07 7.07a1.5 1.5 0 0 1-2.122 0Z" clip-rule="evenodd"/></svg>
                                        {{ $a->center }}
                                    </span>
                                @endif
                            </div>
                            <h2 class="mt-1.5 text-lg sm:text-xl font-bold text-slate-900 transition group-hover:text-brand-700">{{ $a->title }}</h2>

                            {{-- Body, with a Read-more clamp for long reports --}}
                            @if ($long)
                                {{-- Checkbox + direct-sibling targets so peer-checked toggles the clamp --}}
                                <input id="exp-{{ $a->id }}" type="checkbox" class="peer sr-only">
                                <div class="prose prose-sm mt-2 max-w-none overflow-hidden text-slate-600 prose-p:my-2 prose-strong:text-slate-700 [mask-image:linear-gradient(to_bottom,black_70%,transparent)] max-h-40 transition-[max-height] duration-300 peer-checked:max-h-[2000px] peer-checked:[mask-image:none]">{!! $a->body !!}</div>
                                <label for="exp-{{ $a->id }}" class="mt-1 inline-flex cursor-pointer items-center gap-1 text-sm font-semibold text-brand-600 hover:text-brand-700 peer-checked:hidden">
                                    Read more
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                                </label>
                                <label for="exp-{{ $a->id }}" class="mt-1 hidden cursor-pointer items-center gap-1 text-sm font-semibold text-brand-600 hover:text-brand-700 peer-checked:inline-flex">
                                    Read less
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5"/></svg>
                                </label>
                            @else
                                <div class="prose prose-sm mt-2 max-w-none text-slate-600 prose-p:my-2 prose-strong:text-slate-700">{!! $a->body !!}</div>
                            @endif

                            {{-- Photos from this month's album --}}
                            @if ($thumbs->isNotEmpty())
                                <a href="{{ route('public.gallery.show', $gallery->slug) }}" class="mt-4 block">
                                    <p class="mb-2 text-xs font-medium text-slate-400">Photos from {{ $a->date->format('F Y') }}</p>
                                    <div class="flex gap-2">
                                        @foreach ($thumbs as $item)
                                            <span class="relative h-16 w-16 overflow-hidden rounded-lg ring-1 ring-slate-200 sm:h-20 sm:w-20">
                                                <img src="{{ $item->thumbUrl() }}" alt="" loading="lazy" class="h-full w-full object-cover transition group-hover:scale-105">
                                                @if ($loop->last && $gallery->items->count() > 4)
                                                    <span class="absolute inset-0 flex items-center justify-center bg-black/50 text-sm font-bold text-white">+{{ $gallery->items->count() - 4 }}</span>
                                                @endif
                                            </span>
                                        @endforeach
                                    </div>
                                </a>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-10">{{ $activities->links() }}</div>
        @endif
    </div>
@endsection
