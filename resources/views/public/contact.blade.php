@extends('layouts.public')

@section('title', 'Contact & Admissions — Samutkarsh IAS Academy')

@php
    use App\Models\Setting;
    $email    = Setting::get('contact.email', 'samutkarshias@gmail.com');
    $mapEmbed = Setting::get('contact.map_embed');
@endphp

@section('content')
    <div class="mx-auto max-w-5xl px-4 py-12">
        <h1 class="text-2xl font-bold text-slate-900">Contact &amp; Admissions</h1>
        <p class="mt-1 text-sm text-slate-600">We'd love to hear from you. Reach a center or send us a message.</p>

        @if (session('status'))
            <div class="mt-6 rounded-lg bg-green-50 p-4 text-sm text-green-700 ring-1 ring-green-200">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="mt-6 rounded-lg bg-red-50 p-4 text-sm text-red-700 ring-1 ring-red-200">
                <ul class="list-disc list-inside space-y-1">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="mt-8 grid gap-8 lg:grid-cols-2">
            {{-- Centers (single source of truth) --}}
            <div class="space-y-6">
                @forelse ($centers as $center)
                    <div class="rounded-xl bg-white p-6 ring-1 ring-slate-200">
                        <h2 class="font-semibold text-slate-900">
                            {{ $center->name }}
                            @if ($center->is_head_office)
                                <span class="ml-2 rounded bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-700">Head Office</span>
                            @endif
                        </h2>
                        @if ($center->is_physical && $center->address)
                            <p class="mt-2 text-sm text-slate-600">{{ $center->address }}</p>
                        @endif
                        @if ($center->contact_phone)
                            <p class="mt-1 text-sm text-slate-600">Phone:
                                <a href="tel:{{ preg_replace('/\s/', '', $center->contact_phone) }}" class="text-indigo-600">{{ $center->contact_phone }}</a>
                            </p>
                        @endif
                        @if ($center->contact_email)
                            <p class="text-sm text-slate-600">Email: <a href="mailto:{{ $center->contact_email }}" class="text-indigo-600">{{ $center->contact_email }}</a></p>
                        @endif
                        @if ($center->contact_timing)
                            <p class="mt-2 flex items-start gap-1.5 text-sm text-slate-600">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-brand-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                <span><span class="font-medium text-slate-700">Timing:</span> {{ $center->contact_timing }}</span>
                            </p>
                        @endif
                        @if ($center->holiday_info)
                            <p class="mt-2 rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-800 ring-1 ring-amber-100">
                                <span class="font-semibold">Holidays:</span> {{ $center->holiday_info }}
                            </p>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Center details will appear here once centers are added.</p>
                @endforelse

                <p class="text-sm text-slate-600">General email: <a href="mailto:{{ $email }}" class="text-indigo-600">{{ $email }}</a></p>

                @if ($mapEmbed)
                    <div class="overflow-hidden rounded-xl ring-1 ring-slate-200">
                        <iframe src="{{ $mapEmbed }}" width="100%" height="240" style="border:0;" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                @endif
            </div>

            {{-- Inquiry form --}}
            <form method="POST" action="{{ route('public.contact.store') }}" class="rounded-xl bg-white p-6 ring-1 ring-slate-200 space-y-4">
                @csrf
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="mt-1 w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Mobile <span class="text-red-500">*</span></label>
                        <input type="tel" name="phone" value="{{ old('phone') }}" required inputmode="numeric" class="mt-1 w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="mt-1 w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Interested center</label>
                        <select name="center_id" class="mt-1 w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">— Any / not sure —</option>
                            @foreach ($centers as $center)
                                <option value="{{ $center->id }}" @selected(old('center_id') == $center->id)>{{ $center->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Subject</label>
                    <input type="text" name="subject" value="{{ old('subject') }}" class="mt-1 w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Message <span class="text-red-500">*</span></label>
                    <textarea name="message" rows="4" required class="mt-1 w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">{{ old('message') }}</textarea>
                </div>
                <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-3 font-semibold text-white hover:bg-indigo-700">Send message</button>
            </form>
        </div>
    </div>
@endsection
