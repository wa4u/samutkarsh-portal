@extends('layouts.public')

@section('title', $achiever->name . ' — ' . $achiever->headline . ' — Samutkarsh IAS Academy')
@section('og_title', $achiever->name . ' — ' . $achiever->headline)
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($achiever->excerpt ?: $achiever->story), 155))
@if ($achiever->photoUrl())
    @section('og_image', url($achiever->photoUrl()))
@endif

@section('content')
    <article class="mx-auto max-w-3xl px-4 py-12">
        <a href="{{ route('public.achievers.index') }}" class="text-sm font-medium text-brand-600 hover:underline">&larr; All achievers</a>

        <div class="mt-6 grid gap-6 sm:grid-cols-[200px_1fr] sm:items-start">
            @if ($achiever->photoUrl())
                <img src="{{ $achiever->photoUrl() }}" alt="{{ $achiever->name }}"
                     class="w-full max-w-[200px] rounded-2xl object-cover ring-1 ring-slate-200">
            @else
                <div class="flex aspect-[4/5] w-full max-w-[200px] items-center justify-center rounded-2xl bg-gradient-to-br from-brand-500 to-brand-700 text-5xl font-extrabold text-white">
                    {{ \Illuminate\Support\Str::of($achiever->name)->trim()->substr(0, 1)->upper() }}
                </div>
            @endif

            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">{{ $achiever->name }}</h1>
                <p class="mt-2 inline-block rounded-lg bg-brand-50 px-3 py-1.5 text-sm font-bold text-brand-700 ring-1 ring-brand-100">{{ $achiever->headline }}</p>
                @if ($achiever->meta())
                    <p class="mt-3 text-sm text-slate-500">{{ $achiever->meta() }}</p>
                @endif
            </div>
        </div>

        @if ($achiever->story)
            <div class="prose prose-slate mt-8 max-w-none">
                {!! $achiever->story !!}
            </div>
        @elseif ($achiever->excerpt)
            <p class="mt-8 text-slate-700">{{ $achiever->excerpt }}</p>
        @endif

        <div class="mt-10 rounded-2xl bg-brand-50 p-6 text-center ring-1 ring-brand-100">
            <p class="font-semibold text-slate-900">Begin your own journey with Samutkarsh.</p>
            @if ((bool) config('admissions.registration_open'))
                <a href="{{ route('public.register.create') }}" class="mt-3 inline-flex items-center rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-brand-700">Register now</a>
            @endif
        </div>
    </article>
@endsection
