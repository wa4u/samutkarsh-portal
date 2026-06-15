@php
    $c = $c ?? [];
    $heading = $c['heading'] ?? 'Begin your journey to public service';
    $body    = $c['body'] ?? 'Join a community that prepares you not just to clear an exam, but to serve the nation.';
    $primary = $c['cta_primary'] ?? 'Register now';
    $secondary = $c['cta_secondary'] ?? 'Talk to us';
@endphp

{{-- Closing CTA --}}
<section class="bg-gradient-to-r from-brand-600 to-brand-700">
    <div class="mx-auto max-w-5xl px-4 py-16 text-center text-white">
        <h2 class="text-3xl font-extrabold tracking-tight">{{ $heading }}</h2>
        @if ($body)<p class="mt-3 text-white/90 max-w-2xl mx-auto">{{ $body }}</p>@endif
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            @if ($regOpen)
                <a href="{{ route('public.register.create') }}" class="rounded-lg bg-white px-6 py-3 font-bold text-brand-700 hover:bg-brand-50 transition">{{ $primary }}</a>
            @endif
            <a href="{{ route('public.contact') }}" class="rounded-lg border border-white/50 px-6 py-3 font-bold text-white hover:bg-white/10 transition">{{ $secondary }}</a>
        </div>
    </div>
</section>
