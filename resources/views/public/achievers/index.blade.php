@extends('layouts.public')

@section('title', 'Our Achievers — Samutkarsh IAS Academy')
@section('meta_description', 'Meet the Samutkarsh students who went on to do extraordinary things after our programmes.')

@section('content')
    <div class="mx-auto max-w-6xl px-4 py-12">
        <div class="max-w-2xl">
            <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Our Achievers</h1>
            <p class="mt-2 text-slate-600">Students who went on to do something extraordinary after their Samutkarsh journey. Their success is our pride.</p>
        </div>

        @if ($achievers->isEmpty())
            <p class="mt-12 text-center text-slate-500">Achiever stories will appear here soon.</p>
        @else
            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($achievers as $achiever)
                    <a href="{{ route('public.achievers.show', $achiever->slug) }}"
                       class="group flex flex-col overflow-hidden rounded-2xl bg-white ring-1 ring-slate-200 shadow-sm transition hover:shadow-md">
                        <div class="aspect-[4/5] overflow-hidden bg-slate-100">
                            @if ($achiever->photoUrl())
                                <img src="{{ $achiever->photoUrl() }}" alt="{{ $achiever->name }}" loading="lazy"
                                     class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                            @else
                                <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-brand-500 to-brand-700 text-5xl font-extrabold text-white">
                                    {{ \Illuminate\Support\Str::of($achiever->name)->trim()->substr(0, 1)->upper() }}
                                </div>
                            @endif
                        </div>
                        <div class="flex flex-1 flex-col p-5">
                            <h2 class="font-bold text-slate-900 group-hover:text-brand-700">{{ $achiever->name }}</h2>
                            <p class="mt-1 text-sm font-semibold text-brand-600">{{ $achiever->headline }}</p>
                            @if ($achiever->meta())
                                <p class="mt-1 text-xs text-slate-500">{{ $achiever->meta() }}</p>
                            @endif
                            @if ($achiever->excerpt)
                                <p class="mt-3 line-clamp-3 text-sm text-slate-600">{{ $achiever->excerpt }}</p>
                            @endif
                            <span class="mt-4 text-sm font-semibold text-brand-600 group-hover:underline">Read story &rarr;</span>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-8">{{ $achievers->links() }}</div>
        @endif
    </div>
@endsection
