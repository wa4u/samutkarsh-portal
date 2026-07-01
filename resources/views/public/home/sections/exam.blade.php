@php
    $c = $c ?? [];
    $heading   = $c['heading'] ?? 'Entrance Exam Schedule';
    $intro     = $c['intro'] ?? 'Mark your calendar — admission test dates for our centres.';
    $reporting = $c['reporting'] ?? '9:30 AM';
    $examTime  = $c['exam_time'] ?? '10:00 AM to 12:00 PM';
    $note      = $c['note'] ?? null;
    $centres   = $c['centres'] ?? null;
    if (! is_array($centres)) {
        $centres = [
            ['name' => 'Hubli Centre', 'dates' => '12th July'],
            ['name' => 'Belagavi Centre', 'dates' => '5th & 12th July'],
        ];
    }
@endphp

{{-- Exam dates banner — high-visibility, near the top of the home page.
     Uses only Tailwind classes already present elsewhere (no asset rebuild). --}}
<section class="bg-gradient-to-r from-brand-600 to-brand-700 text-white">
    <div class="mx-auto max-w-6xl px-4 py-16">
        <div class="grid gap-12 lg:grid-cols-2 lg:items-center">
            <div>
                <span class="inline-flex items-center rounded-full bg-white/10 px-3 py-1 text-sm font-semibold uppercase tracking-wide ring-1 ring-white/15">Admission Test</span>
                <h2 class="mt-4 text-3xl font-extrabold tracking-tight">{{ $heading }}</h2>
                @if ($intro)<p class="mt-3 text-white/90">{{ $intro }}</p>@endif

                <div class="mt-6 flex flex-wrap gap-3">
                    @if ($reporting)
                        <div class="rounded-lg bg-white/10 px-6 py-3 ring-1 ring-white/15">
                            <p class="text-xs font-semibold uppercase tracking-wide text-white/80">Reporting time</p>
                            <p class="mt-1 text-lg font-bold">{{ $reporting }}</p>
                        </div>
                    @endif
                    @if ($examTime)
                        <div class="rounded-lg bg-white/10 px-6 py-3 ring-1 ring-white/15">
                            <p class="text-xs font-semibold uppercase tracking-wide text-white/80">Exam</p>
                            <p class="mt-1 text-lg font-bold">{{ $examTime }}</p>
                        </div>
                    @endif
                </div>
                @if ($note)<p class="mt-4 text-sm text-white/80">{{ $note }}</p>@endif
            </div>

            <div class="grid grid-cols-2 gap-5">
                @foreach ($centres as $centre)
                    <div class="rounded-2xl bg-white p-6 ring-1 ring-white/10">
                        <p class="text-xs font-semibold uppercase tracking-wide text-brand-600">Centre</p>
                        <p class="mt-1 text-lg font-extrabold text-slate-900">{{ is_array($centre) ? ($centre['name'] ?? '') : '' }}</p>
                        <p class="mt-3 text-lg font-bold text-brand-700">{{ is_array($centre) ? ($centre['dates'] ?? '') : '' }}</p>
                        @if ($regOpen)
                            <a href="{{ route('public.register.create') }}" class="mt-4 inline-flex items-center rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-brand-700 transition">Register now</a>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
