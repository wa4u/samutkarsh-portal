@extends('layouts.public')

@section('title', 'Admission status')

@section('content')
    <div class="mx-auto max-w-lg px-4 py-12">
        <a href="{{ route('public.result.form') }}" class="text-sm text-brand-600 hover:underline">&larr; Check another number</a>

        @if ($results->isEmpty())
            <div class="mt-4 rounded-xl bg-white p-8 ring-1 ring-slate-200 text-center">
                <h1 class="text-xl font-bold text-slate-900">No record found</h1>
                <p class="mt-2 text-slate-600">We couldn't find a {{ $year }} registration for that center and mobile number. Please re-check, or register first.</p>
            </div>
        @else
            @if ($results->count() > 1)
                <p class="mt-4 text-sm text-slate-500">{{ $results->count() }} applications found on this number.</p>
            @endif

            @php
                $map = [
                    'pending'      => ['Under review', 'bg-amber-100 text-amber-800', 'The application is being processed. Please check back later.'],
                    'selected'     => ['Selected for admission', 'bg-indigo-100 text-indigo-800', 'Congratulations! Confirm the seat by paying the admission fee.'],
                    'not_selected' => ['Not selected', 'bg-red-100 text-red-800', 'We regret to inform that this application was not selected this cycle.'],
                    'admitted'     => ['Admitted', 'bg-green-100 text-green-800', 'The seat is confirmed. Welcome to Samutkarsh!'],
                ];
            @endphp

            <div class="mt-4 space-y-4">
                @foreach ($results as $row)
                    @php
                        $reg = $row['registration'];
                        [$label, $badge, $message] = $map[$reg->status] ?? ['Submitted', 'bg-slate-100 text-slate-700', 'Your application has been received.'];
                    @endphp
                    <div class="rounded-xl bg-white p-8 ring-1 ring-slate-200">
                        <p class="text-sm text-slate-500">{{ $reg->center->name }} · {{ $year }}</p>
                        <h2 class="mt-1 text-xl font-bold text-slate-900">{{ $row['student']->name }}</h2>

                        <div class="mt-4">
                            <span class="inline-flex rounded-full px-3 py-1 text-sm font-semibold {{ $badge }}">{{ $label }}</span>
                        </div>

                        <p class="mt-4 text-slate-600">{{ $message }}</p>

                        @if ($reg->status === 'selected' && $row['checkoutUrl'])
                            <a href="{{ $row['checkoutUrl'] }}"
                               class="mt-6 inline-flex w-full justify-center rounded-lg bg-green-600 px-4 py-3 font-semibold text-white hover:bg-green-700">
                                Pay &amp; confirm seat
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
