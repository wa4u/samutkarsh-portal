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
                <p class="mt-3 text-slate-500">No activities found for this filter.</p>
            </div>
        @else
            <div class="space-y-5">
                @foreach ($activities as $a)
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
                            <div class="prose prose-sm mt-2 max-w-none text-slate-600 prose-p:my-2 prose-strong:text-slate-700">{!! $a->body !!}</div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-10">{{ $activities->links() }}</div>
        @endif
    </div>
@endsection
