@extends('layouts.public')

@section('title', 'What parents say — Samutkarsh IAS Academy')
@section('meta_description', 'Messages and feedback from parents and students after Samutkarsh programmes and events.')

@section('content')
    @php use Illuminate\Support\Str; @endphp

    <section class="bg-gradient-to-r from-brand-600 to-brand-700 text-white">
        <div class="mx-auto max-w-5xl px-4 py-12">
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">What parents say</h1>
            <p class="mt-2 text-white/90">Messages from parents and students after our programmes and events.</p>
        </div>
    </section>

    <div class="mx-auto max-w-6xl px-4 py-12">
        @if ($testimonials->isEmpty())
            <p class="text-center text-slate-500">No messages published yet.</p>
        @else
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($testimonials as $t)
                    <figure class="flex flex-col rounded-2xl bg-white p-6 ring-1 ring-slate-200 shadow-sm">
                        <svg class="h-7 w-7 text-brand-300" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M7.2 6A5.2 5.2 0 0 0 4 10.8V18h6.4v-7.2H6.8A3.6 3.6 0 0 1 10 7.2L7.2 6Zm9.6 0a5.2 5.2 0 0 0-3.2 4.8V18H20v-7.2h-3.6A3.6 3.6 0 0 1 19.6 7.2L16.8 6Z"/></svg>
                        <blockquote class="mt-3 flex-1 text-sm leading-relaxed text-slate-700 whitespace-pre-line">{{ $t->body }}</blockquote>
                        <figcaption class="mt-5 flex items-center gap-3 border-t border-slate-100 pt-4">
                            @if ($t->photoUrl())
                                <img src="{{ $t->photoUrl() }}" alt="" class="h-9 w-9 rounded-full object-cover">
                            @else
                                <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-brand-100 text-sm font-bold text-brand-700">{{ Str::of($t->author_name)->trim()->substr(0, 1)->upper() }}</span>
                            @endif
                            <span class="min-w-0">
                                <span class="block truncate font-semibold text-slate-900">{{ $t->author_name }}</span>
                                @if ($t->role || $t->event)
                                    <span class="block truncate text-xs text-slate-500">{{ collect([$t->role, $t->event])->filter()->implode(' · ') }}</span>
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
