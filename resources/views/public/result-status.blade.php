@extends('layouts.public')

@section('title', 'Admission status')

@section('content')
    <div class="mx-auto max-w-lg px-4 py-12">
        <a href="{{ route('public.result.form') }}" class="text-sm text-indigo-600 hover:underline">&larr; Check another number</a>

        @if (! $registration)
            <div class="mt-4 rounded-xl bg-white p-8 ring-1 ring-slate-200 text-center">
                <h1 class="text-xl font-bold text-slate-900">No record found</h1>
                <p class="mt-2 text-slate-600">We couldn't find a {{ $year }} registration for that center and mobile number. Please re-check, or register first.</p>
            </div>
        @else
            @php
                $map = [
                    'pending'      => ['Under review', 'bg-amber-100 text-amber-800', 'Your application is being processed. Please check back later.'],
                    'selected'     => ['Selected', 'bg-indigo-100 text-indigo-800', 'Congratulations! Confirm your seat by paying the admission fee.'],
                    'not_selected' => ['Not selected', 'bg-red-100 text-red-800', 'We regret to inform you were not selected this cycle.'],
                    'admitted'     => ['Admitted', 'bg-green-100 text-green-800', 'Your seat is confirmed. Welcome to Samutkarsh!'],
                ];
                [$label, $badge, $message] = $map[$registration->status];
            @endphp

            <div class="mt-4 rounded-xl bg-white p-8 ring-1 ring-slate-200">
                <p class="text-sm text-slate-500">{{ $registration->center->name }} · {{ $year }}</p>
                <h1 class="mt-1 text-xl font-bold text-slate-900">{{ $student->name }}</h1>

                <div class="mt-4 flex items-center gap-3">
                    <span class="inline-flex rounded-full px-3 py-1 text-sm font-semibold {{ $badge }}">{{ $label }}</span>
                    @if (! is_null($registration->exam_marks))
                        <span class="text-sm text-slate-600">Marks: <strong>{{ $registration->exam_marks }}</strong></span>
                    @endif
                </div>

                <p class="mt-4 text-slate-600">{{ $message }}</p>

                @if ($registration->status === 'selected' && $checkoutUrl)
                    <a href="{{ $checkoutUrl }}"
                       class="mt-6 inline-flex w-full justify-center rounded-lg bg-green-600 px-4 py-3 font-semibold text-white hover:bg-green-700">
                        Pay & confirm seat
                    </a>
                @endif
            </div>
        @endif
    </div>
@endsection
