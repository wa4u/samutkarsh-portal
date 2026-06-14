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
