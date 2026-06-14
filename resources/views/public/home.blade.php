@extends('layouts.public')

@section('title', 'Samutkarsh IAS Academy — Nation Building through IAS')

@section('content')
    @php
        // Shared data for the section partials (inherited via @include).
        $regOpen = (bool) config('admissions.registration_open');
        $heroTitle = \App\Models\Setting::get('site.hero_title', 'Shape your civil services journey');
        $heroSub   = \App\Models\Setting::get('site.hero_subtitle', 'Nation Building through IAS — from school foundation to civil services, across Karnataka.');
        $heroImage = \App\Models\Setting::get('site.hero_image');
        $heroVideo = \App\Models\Setting::get('site.hero_video');
    @endphp

    {{-- Sections are admin-managed (admin → Home page): toggle on/off + drag to reorder. --}}
    @foreach ($homeSections as $key)
        @includeIf('public.home.sections.' . $key)
    @endforeach
@endsection
