@extends('layouts.public')

@section('title', 'What parents & students say — Samutkarsh IAS Academy')
@section('meta_description', 'Messages and feedback from parents and students after Samutkarsh programmes and events, across our centres.')

@section('content')
    @php use Illuminate\Support\Str; @endphp

    {{-- Hero --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-brand-600 via-brand-600 to-brand-700 text-white">
        <div class="absolute inset-0 opacity-10" style="background-image:radial-gradient(circle at 20% 20%, #fff 1px, transparent 1px); background-size:22px 22px;"></div>
        <div class="relative mx-auto max-w-5xl px-4 py-14 sm:py-16">
            <p class="text-sm font-semibold uppercase tracking-widest text-white/70">In their own words</p>
            <h1 class="mt-2 text-3xl sm:text-4xl font-extrabold tracking-tight">What parents &amp; students say</h1>
            <p class="mt-3 max-w-2xl text-white/90">Real messages from our families after sessions, events and field visits — straight from the Samutkarsh community.</p>
        </div>
    </section>

    <div class="mx-auto max-w-6xl px-4 py-10 sm:py-12">

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

        @if ($testimonials->isEmpty())
            <p class="rounded-2xl bg-white py-16 text-center text-slate-500 ring-1 ring-slate-200">No messages published yet.</p>
        @else
            {{-- Masonry-style columns so varied lengths pack tightly --}}
            <div class="columns-1 gap-6 sm:columns-2 lg:columns-3 [column-fill:_balance]">
                @foreach ($testimonials as $t)
                    <figure class="mb-6 break-inside-avoid rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 transition hover:shadow-md">
                        <svg class="h-8 w-8 text-brand-200" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M7.2 6A5.2 5.2 0 0 0 4 10.8V18h6.4v-7.2H6.8A3.6 3.6 0 0 1 10 7.2L7.2 6Zm9.6 0a5.2 5.2 0 0 0-3.2 4.8V18H20v-7.2h-3.6A3.6 3.6 0 0 1 19.6 7.2L16.8 6Z"/></svg>
                        <blockquote class="mt-3 whitespace-pre-line text-[15px] leading-relaxed text-slate-700">{{ $t->body }}</blockquote>
                        <figcaption class="mt-5 flex items-center gap-3 border-t border-slate-100 pt-4">
                            @if ($t->photoUrl())
                                <img src="{{ $t->photoUrl() }}" alt="" class="h-10 w-10 rounded-full object-cover">
                            @else
                                <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-brand-100 text-base font-bold text-brand-700">{{ Str::of($t->author_name)->trim()->substr(0, 1)->upper() }}</span>
                            @endif
                            <span class="min-w-0">
                                <span class="block truncate font-semibold text-slate-900">{{ $t->author_name }}</span>
                                @php $meta = collect([$t->role, $t->center, optional($t->date)->format('M Y')])->filter()->implode(' · '); @endphp
                                @if ($meta)
                                    <span class="block truncate text-xs text-slate-500">{{ $meta }}</span>
                                @endif
                            </span>
                        </figcaption>
                    </figure>
                @endforeach
            </div>

            <div class="mt-10">{{ $testimonials->links() }}</div>
        @endif
    </div>
@endsection
