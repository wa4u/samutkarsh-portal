@extends('layouts.public')

@section('title', 'Activities & sessions — Samutkarsh IAS Academy')
@section('meta_description', 'Week-by-week reports of Samutkarsh sessions, events and field activities across our centres.')

@section('content')
    <section class="bg-gradient-to-r from-brand-600 to-brand-700 text-white">
        <div class="mx-auto max-w-5xl px-4 py-12">
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">Our activities</h1>
            <p class="mt-2 text-white/90">Week by week — what happens in our sessions, events and field visits across centres.</p>
        </div>
    </section>

    <div class="mx-auto max-w-4xl px-4 py-12">
        @if ($activities->isEmpty())
            <p class="text-center text-slate-500">No activities published yet.</p>
        @else
            <ol class="relative border-s-2 border-brand-100 ms-3 space-y-10">
                @foreach ($activities as $a)
                    <li class="ms-6">
                        <span class="absolute -start-[9px] mt-1.5 h-4 w-4 rounded-full bg-brand-500 ring-4 ring-white"></span>
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm">
                            <time class="font-semibold text-brand-700">{{ $a->date->format('d M Y') }}</time>
                            @if ($a->center)
                                <span class="inline-flex items-center rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-medium text-brand-700 ring-1 ring-brand-100">{{ $a->center }}</span>
                            @endif
                        </div>
                        <h2 class="mt-1 text-lg font-bold text-slate-900">{{ $a->title }}</h2>
                        <div class="prose prose-sm mt-2 max-w-none text-slate-700">{!! $a->body !!}</div>
                    </li>
                @endforeach
            </ol>

            <div class="mt-12">{{ $activities->links() }}</div>
        @endif
    </div>
@endsection
