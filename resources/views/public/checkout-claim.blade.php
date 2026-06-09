@extends('layouts.public')

@section('title', 'UPI reference received')

@section('content')
    <div class="mx-auto max-w-xl px-4 py-20 text-center">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-amber-100">
            <svg class="h-7 w-7 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
        </div>
        <h1 class="mt-6 text-2xl font-bold text-slate-900">UPI reference received</h1>
        <p class="mt-2 text-slate-600">
            Thanks! We've recorded UTR <strong>{{ $utr }}</strong> for {{ $registration->center->name }}.
            Your seat is confirmed once the center verifies the payment — check the Result Gateway for updates.
        </p>
        <a href="{{ route('public.result.form') }}" class="mt-8 inline-flex rounded-lg bg-indigo-600 px-5 py-2.5 font-semibold text-white hover:bg-indigo-700">Check status</a>
    </div>
@endsection
