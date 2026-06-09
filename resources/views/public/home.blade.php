@extends('layouts.public')

@section('title', 'Samutkarsh IAS Academy — Admissions')

@section('content')
    <section class="bg-gradient-to-b from-indigo-600 to-indigo-700 text-white">
        <div class="mx-auto max-w-5xl px-4 py-20 text-center">
            <h1 class="text-4xl sm:text-5xl font-bold tracking-tight">Shape your civil services journey</h1>
            <p class="mt-4 text-lg text-indigo-100 max-w-2xl mx-auto">
                Admissions for {{ config('admissions.academic_year') }} are
                {{ config('admissions.registration_open') ? 'open' : 'closed' }} across all Samutkarsh centers.
            </p>
            <div class="mt-8 flex flex-wrap justify-center gap-3">
                @if (config('admissions.registration_open'))
                    <a href="{{ route('public.register.create') }}"
                       class="rounded-lg bg-white px-6 py-3 font-semibold text-indigo-700 hover:bg-indigo-50">
                        Register now
                    </a>
                @endif
                <a href="{{ route('public.result.form') }}"
                   class="rounded-lg border border-indigo-300 px-6 py-3 font-semibold text-white hover:bg-indigo-500">
                    Check admission status
                </a>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-5xl px-4 py-16 grid gap-8 sm:grid-cols-3">
        @foreach ([
            ['1. Register', 'Pick your center and submit your details for the current admission cycle.'],
            ['2. Check result', 'Enter your center and phone number in the Result Gateway to see your status.'],
            ['3. Confirm seat', 'If selected, pay the admission fee online, by UPI, or in cash at the center.'],
        ] as [$title, $body])
            <div class="rounded-xl bg-white p-6 ring-1 ring-slate-200">
                <h3 class="font-semibold text-slate-900">{{ $title }}</h3>
                <p class="mt-2 text-sm text-slate-600">{{ $body }}</p>
            </div>
        @endforeach
    </section>
@endsection
