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

    {{-- Sections are admin-managed (admin → Home page): toggle on/off, reorder, edit text/media.
         $c = that section's editable content (empty array → partial uses its defaults). --}}
    @foreach ($homeSections as $key => $content)
        @includeIf('public.home.sections.' . $key, ['c' => $content])
    @endforeach
@endsection
