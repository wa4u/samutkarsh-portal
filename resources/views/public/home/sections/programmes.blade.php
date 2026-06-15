@php
    use Illuminate\Support\Str;
    $c = $c ?? [];
    $heading = $c['heading'] ?? 'Our programmes';
    $intro   = $c['intro'] ?? 'From early school foundation to full civil-services coaching — a continuous path, rooted in Bharatiya ethos.';
    $cards   = $c['cards'] ?? [
        ['title' => 'Shraddha & Medha', 'desc' => 'Pre-IAS foundation for Classes 6–9 — officer-like qualities, life skills and values.', 'link' => 'shraddha-medha'],
        ['title' => 'Utkarsh', 'desc' => 'For degree students of any stream — orientation and motivation toward civil services.', 'link' => 'utkarsh'],
        ['title' => 'IAS Coaching', 'desc' => 'Complete UPSC & KPSC preparation with faculty from Sankalp, New Delhi.', 'link' => 'ias-coaching'],
        ['title' => 'Dristi & Disha', 'desc' => 'Short orientation workshops and certificate courses for colleges.', 'link' => 'dristi-disha'],
        ['title' => 'Short Programs', 'desc' => 'Parichaya, Pravinya & Prabuddha — certificate camps for school students.', 'link' => 'short-programs'],
        ['title' => 'Results & Achievements', 'desc' => 'Topper stories and the impact of our mentoring across Karnataka.', 'link' => 'results-achievements'],
    ];
@endphp

{{-- Programmes --}}
<section id="programmes" class="mx-auto max-w-6xl px-4 py-20">
    <div class="text-center max-w-2xl mx-auto">
        <h2 class="text-3xl font-extrabold tracking-tight text-slate-900">{{ $heading }}</h2>
        @if ($intro)<p class="mt-3 text-slate-600">{{ $intro }}</p>@endif
    </div>
    <div class="mt-12 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        @foreach ($cards as $card)
            @php $link = $card['link'] ?? '#'; $href = Str::startsWith($link, ['http://', 'https://', '/']) ? $link : url('/' . ltrim($link, '/')); @endphp
            <a href="{{ $href }}"
               class="group flex flex-col rounded-2xl bg-white p-6 ring-1 ring-slate-200 hover:shadow-lg hover:-translate-y-0.5 transition">
                <div class="h-1.5 w-12 rounded-full bg-gradient-to-r from-brand-500 to-brand-600"></div>
                <h3 class="mt-4 text-lg font-bold text-slate-900 group-hover:text-brand-700">{{ $card['title'] ?? '' }}</h3>
                <p class="mt-2 flex-1 text-sm text-slate-600">{{ $card['desc'] ?? '' }}</p>
                <span class="mt-4 text-sm font-semibold text-brand-600">Learn more &rarr;</span>
            </a>
        @endforeach
    </div>
</section>
