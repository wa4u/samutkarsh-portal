@php
    $c = $c ?? [];
    $heading   = $c['heading'] ?? 'Entrance Exam Schedule';
    $intro     = $c['intro'] ?? 'Mark your calendar — admission test dates for our centres.';
    $reporting = $c['reporting'] ?? '9:30 AM';
    $examTime  = $c['exam_time'] ?? '10:00 AM – 12:00 PM';
    $note      = $c['note'] ?? null;
    $centres   = $c['centres'] ?? [
        ['name' => 'Hubli Centre', 'dates' => '12th July'],
        ['name' => 'Belagavi Centre', 'dates' => '5th & 12th July'],
    ];
@endphp

{{-- Exam dates banner — high-visibility, near the top of the home page --}}
<section class="bg-gradient-to-br from-brand-600 to-brand-700 text-white">
    <div class="mx-auto max-w-6xl px-4 py-12">
        <div class="flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between">
            <div class="max-w-xl">
                <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-bold uppercase tracking-wide ring-1 ring-white/25">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                    Admission Test
                </span>
                <h2 class="mt-4 text-3xl font-extrabold tracking-tight sm:text-4xl">{{ $heading }}</h2>
                @if ($intro)<p class="mt-3 text-white/90">{{ $intro }}</p>@endif

                <div class="mt-6 flex flex-wrap gap-3">
                    @if ($reporting)
                        <div class="rounded-lg bg-white/10 px-4 py-2 ring-1 ring-white/20">
                            <p class="text-xs font-semibold uppercase tracking-wide text-white/70">Reporting time</p>
                            <p class="text-lg font-bold">{{ $reporting }}</p>
                        </div>
                    @endif
                    @if ($examTime)
                        <div class="rounded-lg bg-white/10 px-4 py-2 ring-1 ring-white/20">
                            <p class="text-xs font-semibold uppercase tracking-wide text-white/70">Exam</p>
                            <p class="text-lg font-bold">{{ $examTime }}</p>
                        </div>
                    @endif
                </div>
                @if ($note)<p class="mt-4 text-sm text-white/80">{{ $note }}</p>@endif
            </div>

            @if (! empty($centres))
                <div class="grid w-full gap-4 sm:grid-cols-2 lg:w-auto lg:min-w-[26rem]">
                    @foreach ($centres as $centre)
                        <div class="rounded-2xl bg-white p-5 text-ink-900 shadow-lg ring-1 ring-black/5">
                            <p class="text-xs font-semibold uppercase tracking-wide text-brand-600">Centre</p>
                            <p class="mt-1 text-xl font-extrabold">{{ $centre['name'] ?? '' }}</p>
                            <p class="mt-3 flex items-center gap-2 text-base font-bold text-gray-900">
                                <svg class="h-5 w-5 text-brand-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                                {{ $centre['dates'] ?? '' }}
                            </p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>
