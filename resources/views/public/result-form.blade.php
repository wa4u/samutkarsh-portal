@extends('layouts.public')

@section('title', 'Result Gateway')

@section('content')
    <div class="mx-auto max-w-md px-4 py-12">
        <h1 class="text-2xl font-bold text-slate-900">Result Gateway</h1>
        <p class="mt-1 text-sm text-slate-600">Select your center and enter your registered mobile number to view your {{ $year }} admission status.</p>

        @if ($errors->any())
            <div class="mt-6 rounded-lg bg-red-50 p-4 text-sm text-red-700 ring-1 ring-red-200">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('public.result.lookup') }}" class="mt-8 space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700">Center</label>
                <select name="center_id" required
                        class="mt-1 w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">— Select your center —</option>
                    @foreach ($centers as $center)
                        <option value="{{ $center->id }}" @selected(old('center_id') == $center->id)>{{ $center->name }} ({{ $center->city }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Mobile number</label>
                <input type="tel" name="phone" value="{{ old('phone') }}" required inputmode="numeric"
                       class="mt-1 w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-3 font-semibold text-white hover:bg-indigo-700">
                View status
            </button>
        </form>
    </div>
@endsection
