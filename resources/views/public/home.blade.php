@extends('layouts.public')

@section('title', 'Samutkarsh IAS Academy — Nation Building through IAS')

@section('content')
    @php
        $regOpen = (bool) config('admissions.registration_open');
        $heroTitle = \App\Models\Setting::get('site.hero_title', 'Shape your civil services journey');
        $heroSub   = \App\Models\Setting::get('site.hero_subtitle', 'Nation Building through IAS — from school foundation to civil services, across Karnataka.');
        $heroImage = \App\Models\Setting::get('site.hero_image');
        $heroVideo = \App\Models\Setting::get('site.hero_video');
    @endphp

    {{-- Hero (optional image/video background from Settings; saffron gradient otherwise) --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-brand-500 via-brand-600 to-brand-700 text-white">
        @if ($heroVideo)
            <video class="absolute inset-0 h-full w-full object-cover" autoplay muted loop playsinline
                   @if ($heroImage) poster="{{ $heroImage }}" @endif>
                <source src="{{ $heroVideo }}" type="video/mp4">
            </video>
            <div class="absolute inset-0 bg-gradient-to-br from-brand-700/85 via-brand-800/80 to-ink-900/85"></div>
        @elseif ($heroImage)
            <img src="{{ $heroImage }}" alt="" class="absolute inset-0 h-full w-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-br from-brand-700/85 via-brand-800/80 to-ink-900/85"></div>
        @else
            <div class="absolute -top-24 -right-24 h-72 w-72 rounded-full bg-white/10 blur-2xl"></div>
            <div class="absolute -bottom-32 -left-20 h-80 w-80 rounded-full bg-ink-700/30 blur-3xl"></div>
        @endif
        <div class="relative mx-auto max-w-6xl px-4 py-20 sm:py-28">
            <div class="max-w-3xl">
                <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold uppercase tracking-wide ring-1 ring-white/20">
                    Nation Building through IAS
                </span>
                <h1 class="mt-5 text-4xl sm:text-6xl font-extrabold tracking-tight leading-[1.05]">{{ $heroTitle }}</h1>
                <p class="mt-5 text-lg sm:text-xl text-white/90 max-w-2xl">{{ $heroSub }}</p>
                <p class="mt-3 text-sm text-white/75">
                    Admissions for {{ config('admissions.academic_year') }} are
                    <strong class="font-semibold text-white">{{ $regOpen ? 'open' : 'closed' }}</strong> across all Samutkarsh centres.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    @if ($regOpen)
                        <a href="{{ route('public.register.create') }}"
                           class="rounded-lg bg-white px-6 py-3 font-bold text-brand-700 shadow-sm hover:bg-brand-50 transition">Register now</a>
                    @endif
                    <a href="#programmes"
                       class="rounded-lg bg-ink-800 px-6 py-3 font-bold text-white shadow-sm hover:bg-ink-900 transition">Explore programmes</a>
                    <a href="{{ route('public.result.form') }}"
                       class="rounded-lg border border-white/40 px-6 py-3 font-bold text-white hover:bg-white/10 transition">Check admission status</a>
                </div>
            </div>
        </div>
    </section>

    {{-- Audience quick-links --}}
    <section class="mx-auto max-w-6xl px-4 -mt-10 relative z-10">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['School Students', 'Classes 6–9 foundation', 'shraddha-medha', 'M12 14l9-5-9-5-9 5 9 5z M12 14v7 M5 10.5V16c0 1 3 3 7 3s7-2 7-3v-5.5'],
                ['Degree Students', 'Utkarsh guidance', 'utkarsh', 'M12 6.5c2-2 5-2 7 0v9c-2-2-5-2-7 0m0-9c-2-2-5-2-7 0v9c2-2 5-2 7 0m0-9v9'],
                ['IAS Aspirants', 'Full UPSC / KPSC coaching', 'ias-coaching', 'M3 21h18 M5 21V8l7-4 7 4v13 M9 21v-6h6v6'],
                ['Colleges', 'Dristi & Disha orientation', 'dristi-disha', 'M12 3l9 4.5-9 4.5-9-4.5L12 3z M21 10.5v6 M6.5 12.5v4.2c0 .9 2.5 2.3 5.5 2.3s5.5-1.4 5.5-2.3v-4.2'],
            ] as [$title, $desc, $slug, $icon])
                <a href="{{ url('/' . $slug) }}"
                   class="group rounded-2xl bg-white p-5 ring-1 ring-slate-200 shadow-sm hover:shadow-md hover:ring-brand-300 transition">
                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600 group-hover:bg-brand-600 group-hover:text-white transition">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                    </span>
                    <h3 class="mt-4 font-bold text-slate-900 group-hover:text-brand-700">{{ $title }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ $desc }}</p>
                </a>
            @endforeach
        </div>
    </section>

    {{-- Programmes --}}
    <section id="programmes" class="mx-auto max-w-6xl px-4 py-20">
        <div class="text-center max-w-2xl mx-auto">
            <h2 class="text-3xl font-extrabold tracking-tight text-slate-900">Our programmes</h2>
            <p class="mt-3 text-slate-600">From early school foundation to full civil-services coaching — a continuous path, rooted in Bharatiya ethos.</p>
        </div>
        <div class="mt-12 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ([
                ['Shraddha & Medha', 'Pre-IAS foundation for Classes 6–9 — officer-like qualities, life skills and values.', 'shraddha-medha'],
                ['Utkarsh', 'For degree students of any stream — orientation and motivation toward civil services.', 'utkarsh'],
                ['IAS Coaching', 'Complete UPSC & KPSC preparation with faculty from Sankalp, New Delhi.', 'ias-coaching'],
                ['Dristi & Disha', 'Short orientation workshops and certificate courses for colleges.', 'dristi-disha'],
                ['Short Programs', 'Parichaya, Pravinya & Prabuddha — certificate camps for school students.', 'short-programs'],
                ['Results & Achievements', 'Topper stories and the impact of our mentoring across Karnataka.', 'results-achievements'],
            ] as [$title, $desc, $slug])
                <a href="{{ url('/' . $slug) }}"
                   class="group flex flex-col rounded-2xl bg-white p-6 ring-1 ring-slate-200 hover:shadow-lg hover:-translate-y-0.5 transition">
                    <div class="h-1.5 w-12 rounded-full bg-gradient-to-r from-brand-500 to-brand-600"></div>
                    <h3 class="mt-4 text-lg font-bold text-slate-900 group-hover:text-brand-700">{{ $title }}</h3>
                    <p class="mt-2 flex-1 text-sm text-slate-600">{{ $desc }}</p>
                    <span class="mt-4 text-sm font-semibold text-brand-600">Learn more &rarr;</span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- Why Samutkarsh: values + stats --}}
    <section class="bg-ink-900 text-white">
        <div class="mx-auto max-w-6xl px-4 py-20">
            <div class="grid gap-12 lg:grid-cols-2 lg:items-center">
                <div>
                    <h2 class="text-3xl font-extrabold tracking-tight">Why Samutkarsh</h2>
                    <p class="mt-4 text-white/80">A socially committed Trust formed in 2015 at Hubballi, guided by eminent civil servants, social workers and academicians — building “Centres of Excellence” for civil services across Karnataka.</p>
                    <div class="mt-6 flex flex-wrap gap-2">
                        @foreach (['Excellence', 'Compassion', 'Integrity', 'Knowledge', 'Attitude', 'Skill'] as $value)
                            <span class="rounded-full bg-white/10 px-3 py-1 text-sm font-medium ring-1 ring-white/15">{{ $value }}</span>
                        @endforeach
                    </div>
                    <p class="mt-5 text-brand-300 font-semibold">…together shaping Transformation.</p>
                </div>
                <div class="grid grid-cols-2 gap-5">
                    @foreach ([
                        ['Since', '2015', 'Serving Karnataka'],
                        ['Programmes', '10+', 'School to civil services'],
                        ['Centres', 'Across KA', 'Hubballi · Bengaluru · more'],
                        ['Faculty', 'Sankalp', 'New Delhi expertise'],
                    ] as [$label, $big, $sub])
                        <div class="rounded-2xl bg-white/5 p-6 ring-1 ring-white/10">
                            <p class="text-xs font-semibold uppercase tracking-wide text-white/60">{{ $label }}</p>
                            <p class="mt-1 text-3xl font-extrabold text-brand-400">{{ $big }}</p>
                            <p class="mt-1 text-sm text-white/70">{{ $sub }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- Latest blog --}}
    @if (($latestPosts ?? collect())->isNotEmpty())
        <section class="mx-auto max-w-6xl px-4 py-20">
            <div class="flex items-end justify-between">
                <h2 class="text-3xl font-extrabold tracking-tight text-slate-900">Latest from our blog</h2>
                <a href="{{ route('public.blog.index') }}" class="text-sm font-semibold text-brand-600 hover:underline">View all &rarr;</a>
            </div>
            <div class="mt-10 grid gap-6 sm:grid-cols-3">
                @foreach ($latestPosts as $post)
                    <a href="{{ route('public.blog.show', $post->slug) }}"
                       class="group flex flex-col rounded-2xl overflow-hidden ring-1 ring-slate-200 bg-white hover:shadow-md transition">
                        @if ($post->featureImageUrl())
                            <div class="aspect-[16/9] bg-slate-100 overflow-hidden">
                                <img src="{{ $post->featureImageUrl() }}" alt="{{ $post->title }}" loading="lazy"
                                     class="h-full w-full object-cover group-hover:scale-105 transition duration-300">
                            </div>
                        @else
                            <div class="aspect-[16/9] flex items-center justify-center bg-gradient-to-br from-brand-500 to-brand-700 text-white/90 text-sm font-semibold">Samutkarsh IAS</div>
                        @endif
                        <div class="p-5">
                            @if ($post->category)
                                <span class="text-xs font-semibold text-brand-600">{{ $post->category->name }}</span>
                            @endif
                            <h3 class="mt-1 font-bold text-slate-900 group-hover:text-brand-700">{{ $post->title }}</h3>
                            <span class="mt-2 block text-xs text-slate-400">{{ optional($post->published_at)->format('d M Y') }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Closing CTA --}}
    <section class="bg-gradient-to-r from-brand-600 to-brand-700">
        <div class="mx-auto max-w-5xl px-4 py-16 text-center text-white">
            <h2 class="text-3xl font-extrabold tracking-tight">Begin your journey to public service</h2>
            <p class="mt-3 text-white/90 max-w-2xl mx-auto">Join a community that prepares you not just to clear an exam, but to serve the nation.</p>
            <div class="mt-8 flex flex-wrap justify-center gap-3">
                @if ($regOpen)
                    <a href="{{ route('public.register.create') }}" class="rounded-lg bg-white px-6 py-3 font-bold text-brand-700 hover:bg-brand-50 transition">Register now</a>
                @endif
                <a href="{{ route('public.contact') }}" class="rounded-lg border border-white/50 px-6 py-3 font-bold text-white hover:bg-white/10 transition">Talk to us</a>
            </div>
        </div>
    </section>
@endsection
