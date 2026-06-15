@php
    use Illuminate\Support\Str;
    $c = $c ?? [];
    // Decorative icons reused by position; admin edits title/desc/link.
    $icons = [
        'M12 14l9-5-9-5-9 5 9 5z M12 14v7 M5 10.5V16c0 1 3 3 7 3s7-2 7-3v-5.5',
        'M12 6.5c2-2 5-2 7 0v9c-2-2-5-2-7 0m0-9c-2-2-5-2-7 0v9c2-2 5-2 7 0m0-9v9',
        'M3 21h18 M5 21V8l7-4 7 4v13 M9 21v-6h6v6',
        'M12 3l9 4.5-9 4.5-9-4.5L12 3z M21 10.5v6 M6.5 12.5v4.2c0 .9 2.5 2.3 5.5 2.3s5.5-1.4 5.5-2.3v-4.2',
    ];
    $cards = $c['cards'] ?? [
        ['title' => 'School Students', 'desc' => 'Classes 6–9 foundation', 'link' => 'shraddha-medha'],
        ['title' => 'Degree Students', 'desc' => 'Utkarsh guidance', 'link' => 'utkarsh'],
        ['title' => 'IAS Aspirants', 'desc' => 'Full UPSC / KPSC coaching', 'link' => 'ias-coaching'],
        ['title' => 'Colleges', 'desc' => 'Dristi & Disha orientation', 'link' => 'dristi-disha'],
    ];
@endphp

{{-- Audience quick-links --}}
<section class="mx-auto max-w-6xl px-4 -mt-10 relative z-10">
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($cards as $i => $card)
            @php $link = $card['link'] ?? '#'; $href = Str::startsWith($link, ['http://', 'https://', '/']) ? $link : url('/' . ltrim($link, '/')); @endphp
            <a href="{{ $href }}"
               class="group rounded-2xl bg-white p-5 ring-1 ring-slate-200 shadow-sm hover:shadow-md hover:ring-brand-300 transition">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600 group-hover:bg-brand-600 group-hover:text-white transition">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$i % count($icons)] }}"/></svg>
                </span>
                <h3 class="mt-4 font-bold text-slate-900 group-hover:text-brand-700">{{ $card['title'] ?? '' }}</h3>
                <p class="mt-1 text-sm text-slate-500">{{ $card['desc'] ?? '' }}</p>
            </a>
        @endforeach
    </div>
</section>
