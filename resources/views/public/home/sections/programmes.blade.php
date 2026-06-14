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
