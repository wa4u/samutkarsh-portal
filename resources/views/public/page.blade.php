@extends('layouts.public')

@section('title', $page->title . ' — Samutkarsh IAS Academy')
@section('og_title', $page->title)
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($page->content), 155))

@section('content')
    {{-- Page header band --}}
    <section class="bg-gradient-to-r from-brand-600 to-brand-700 text-white">
        <div class="mx-auto max-w-3xl px-4 py-12">
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">{{ $page->title }}</h1>
        </div>
    </section>
    <div class="mx-auto max-w-3xl px-4 py-12">
        <div class="prose prose-slate max-w-none prose-headings:font-bold prose-a:text-brand-600">
            {!! $page->content !!}
        </div>
    </div>
@endsection
