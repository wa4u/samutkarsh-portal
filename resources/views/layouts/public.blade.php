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
    <header class="bg-white border-b border-slate-200 relative z-40">
        <div class="mx-auto max-w-6xl px-4 h-16 flex items-center justify-between gap-4">
            <a href="{{ route('public.home') }}" class="flex items-center gap-2 font-semibold text-slate-900 shrink-0">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-600 text-white text-sm font-bold">S</span>
                <span class="hidden sm:inline">Samutkarsh IAS Academy</span>
            </a>

            @php
                // Fallback nav if no menu has been configured yet.
                $fallback = collect([
                    (object) ['label' => 'Home', 'href' => route('public.home')],
                    (object) ['label' => 'Blog', 'href' => route('public.blog.index')],
                    (object) ['label' => 'Gallery', 'href' => route('public.gallery.index')],
                    (object) ['label' => 'Register', 'href' => route('public.register.create')],
                    (object) ['label' => 'Check Result', 'href' => route('public.result.form')],
                ]);
            @endphp

            {{-- Desktop nav --}}
            <nav class="hidden md:flex items-center gap-1 text-sm font-medium">
                @forelse ($headerMenu ?? [] as $item)
                    @if ($item->hasChildren())
                        <div class="relative group">
                            <button class="px-3 py-2 rounded-md text-slate-600 hover:text-indigo-700 hover:bg-indigo-50 inline-flex items-center gap-1">
                                {{ $item->label }}
                                <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path d="M5.5 7.5 10 12l4.5-4.5z"/></svg>
                            </button>
                            <div class="absolute left-0 top-full hidden group-hover:block pt-1 w-56">
                                <div class="rounded-lg bg-white py-2 shadow-lg ring-1 ring-slate-200">
                                    @foreach ($item->children as $child)
                                        <a href="{{ $child->url() }}" @if ($child->target_blank) target="_blank" rel="noopener" @endif
                                           class="block px-4 py-2 text-slate-600 hover:bg-indigo-50 hover:text-indigo-700">{{ $child->label }}</a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ $item->url() }}" @if ($item->target_blank) target="_blank" rel="noopener" @endif
                           class="px-3 py-2 rounded-md text-slate-600 hover:text-indigo-700 hover:bg-indigo-50">{{ $item->label }}</a>
                    @endif
                @empty
                    @foreach ($fallback as $item)
                        <a href="{{ $item->href }}" class="px-3 py-2 rounded-md text-slate-600 hover:text-indigo-700 hover:bg-indigo-50">{{ $item->label }}</a>
                    @endforeach
                @endforelse
            </nav>

            {{-- Mobile toggle --}}
            <button id="mobile-menu-btn" class="md:hidden p-2 text-slate-600" aria-label="Menu">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5"/></svg>
            </button>
        </div>

        {{-- Mobile nav --}}
        <nav id="mobile-menu" class="md:hidden hidden border-t border-slate-200 px-4 py-3 space-y-1 text-sm font-medium">
            @forelse ($headerMenu ?? [] as $item)
                @if ($item->hasChildren())
                    <div class="py-1">
                        <p class="px-2 py-1 font-semibold text-slate-900">{{ $item->label }}</p>
                        @foreach ($item->children as $child)
                            <a href="{{ $child->url() }}" @if ($child->target_blank) target="_blank" rel="noopener" @endif
                               class="block pl-5 pr-2 py-2 text-slate-600 hover:text-indigo-700">{{ $child->label }}</a>
                        @endforeach
                    </div>
                @else
                    <a href="{{ $item->url() }}" @if ($item->target_blank) target="_blank" rel="noopener" @endif
                       class="block px-2 py-2 text-slate-600 hover:text-indigo-700">{{ $item->label }}</a>
                @endif
            @empty
                @foreach ($fallback as $item)
                    <a href="{{ $item->href }}" class="block px-2 py-2 text-slate-600 hover:text-indigo-700">{{ $item->label }}</a>
                @endforeach
            @endforelse
        </nav>
    </header>

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="border-t border-slate-200 bg-white">
        <div class="mx-auto max-w-6xl px-4 py-6 text-sm text-slate-500 flex flex-col sm:flex-row justify-between gap-2">
            <span>&copy; {{ date('Y') }} Samutkarsh IAS Academy. All rights reserved.</span>
            <span>Admissions {{ config('admissions.academic_year') }}</span>
        </div>
    </footer>

    <script>
        document.getElementById('mobile-menu-btn')?.addEventListener('click', () => {
            document.getElementById('mobile-menu')?.classList.toggle('hidden');
        });
    </script>

    @stack('scripts')
</body>
</html>
