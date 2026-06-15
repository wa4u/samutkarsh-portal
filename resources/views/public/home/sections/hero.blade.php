@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Storage;
    $c = $c ?? [];
    $resolve = fn (?string $v) => $v ? (Str::startsWith($v, ['http://', 'https://', '/']) ? $v : Storage::disk('public')->url($v)) : null;

    $badge    = $c['badge']    ?? 'Nation Building through IAS';
    $title    = $c['title']    ?? ($heroTitle ?? 'Shape your civil services journey');
    $subtitle = $c['subtitle'] ?? ($heroSub ?? '');
    $imgUrl   = $resolve($c['image'] ?? ($heroImage ?: null));
    $vidUrl   = $resolve($c['video'] ?? ($heroVideo ?: null));
    $ctaPrimary = $c['cta_primary'] ?? 'Register now';
    $ctaExplore = $c['cta_explore'] ?? 'Explore programmes';
    $ctaStatus  = $c['cta_status']  ?? 'Check admission status';
@endphp

{{-- Hero (optional image/video background from admin; saffron gradient otherwise) --}}
<section class="relative overflow-hidden bg-gradient-to-br from-brand-500 via-brand-600 to-brand-700 text-white">
    @if ($vidUrl)
        <video class="absolute inset-0 h-full w-full object-cover" autoplay muted loop playsinline
               @if ($imgUrl) poster="{{ $imgUrl }}" @endif>
            <source src="{{ $vidUrl }}" type="video/mp4">
        </video>
        <div class="absolute inset-0 bg-gradient-to-br from-brand-700/85 via-brand-800/80 to-ink-900/85"></div>
    @elseif ($imgUrl)
        <img src="{{ $imgUrl }}" alt="" class="absolute inset-0 h-full w-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-br from-brand-700/85 via-brand-800/80 to-ink-900/85"></div>
    @else
        <div class="absolute -top-24 -right-24 h-72 w-72 rounded-full bg-white/10 blur-2xl"></div>
        <div class="absolute -bottom-32 -left-20 h-80 w-80 rounded-full bg-ink-700/30 blur-3xl"></div>
    @endif
    <div class="relative mx-auto max-w-6xl px-4 py-20 sm:py-28">
        <div class="max-w-3xl">
            @if ($badge)
                <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold uppercase tracking-wide ring-1 ring-white/20">{{ $badge }}</span>
            @endif
            <h1 class="mt-5 text-4xl sm:text-6xl font-extrabold tracking-tight leading-[1.05]">{{ $title }}</h1>
            @if ($subtitle)
                <p class="mt-5 text-lg sm:text-xl text-white/90 max-w-2xl">{{ $subtitle }}</p>
            @endif
            <p class="mt-3 text-sm text-white/75">
                Admissions for {{ config('admissions.academic_year') }} are
                <strong class="font-semibold text-white">{{ $regOpen ? 'open' : 'closed' }}</strong> across all Samutkarsh centres.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                @if ($regOpen)
                    <a href="{{ route('public.register.create') }}"
                       class="rounded-lg bg-white px-6 py-3 font-bold text-brand-700 shadow-sm hover:bg-brand-50 transition">{{ $ctaPrimary }}</a>
                @endif
                <a href="#programmes"
                   class="rounded-lg bg-ink-800 px-6 py-3 font-bold text-white shadow-sm hover:bg-ink-900 transition">{{ $ctaExplore }}</a>
                <a href="{{ route('public.result.form') }}"
                   class="rounded-lg border border-white/40 px-6 py-3 font-bold text-white hover:bg-white/10 transition">{{ $ctaStatus }}</a>
            </div>
        </div>
    </div>
</section>
