@extends('layouts.public')

@section('title', 'Seat confirmed')

@section('content')
    <div class="mx-auto max-w-xl px-4 py-20 text-center">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-green-100">
            <svg class="h-7 w-7 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
            </svg>
        </div>
        <h1 class="mt-6 text-2xl font-bold text-slate-900">Seat already confirmed</h1>
        <p class="mt-2 text-slate-600">
            {{ $registration->student->name }}, your admission for {{ $registration->academic_year }} is confirmed. No further payment is needed.
        </p>
        <a href="{{ route('public.home') }}" class="mt-8 inline-flex rounded-lg bg-indigo-600 px-5 py-2.5 font-semibold text-white hover:bg-indigo-700">Home</a>
    </div>
@endsection
