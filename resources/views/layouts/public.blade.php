<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Samutkarsh IAS Academy')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-full flex flex-col text-slate-800 antialiased">
    <header class="bg-white border-b border-slate-200">
        <div class="mx-auto max-w-5xl px-4 h-16 flex items-center justify-between">
            <a href="{{ route('public.home') }}" class="flex items-center gap-2 font-semibold text-slate-900">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-600 text-white text-sm font-bold">S</span>
                Samutkarsh IAS Academy
            </a>
            <nav class="flex items-center gap-1 text-sm font-medium">
                <a href="{{ route('public.register.create') }}" class="px-3 py-2 rounded-md text-slate-600 hover:text-indigo-700 hover:bg-indigo-50">Register</a>
                <a href="{{ route('public.result.form') }}" class="px-3 py-2 rounded-md text-slate-600 hover:text-indigo-700 hover:bg-indigo-50">Check Result</a>
            </nav>
        </div>
    </header>

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="border-t border-slate-200 bg-white">
        <div class="mx-auto max-w-5xl px-4 py-6 text-sm text-slate-500 flex flex-col sm:flex-row justify-between gap-2">
            <span>&copy; {{ date('Y') }} Samutkarsh IAS Academy. All rights reserved.</span>
            <span>Admissions {{ config('admissions.academic_year') }}</span>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
