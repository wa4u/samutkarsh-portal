@php
    $c = $c ?? [];
    $heading = $c['heading'] ?? 'Why Samutkarsh';
    $body    = $c['body'] ?? 'A socially committed Trust formed in 2015 at Hubballi, guided by eminent civil servants, social workers and academicians — building “Centres of Excellence” for civil services across Karnataka.';
    $closing = $c['closing'] ?? '…together shaping Transformation.';
    $values  = $c['values'] ?? ['Excellence', 'Compassion', 'Integrity', 'Knowledge', 'Attitude', 'Skill'];
    $stats   = $c['stats'] ?? [
        ['label' => 'Since', 'value' => '2015', 'caption' => 'Serving Karnataka'],
        ['label' => 'Programmes', 'value' => '10+', 'caption' => 'School to civil services'],
        ['label' => 'Centres', 'value' => 'Across KA', 'caption' => 'Hubballi · Bengaluru · more'],
        ['label' => 'Faculty', 'value' => 'Sankalp', 'caption' => 'New Delhi expertise'],
    ];
@endphp

{{-- Why Samutkarsh: values + stats --}}
<section class="bg-ink-900 text-white">
    <div class="mx-auto max-w-6xl px-4 py-20">
        <div class="grid gap-12 lg:grid-cols-2 lg:items-center">
            <div>
                <h2 class="text-3xl font-extrabold tracking-tight">{{ $heading }}</h2>
                <p class="mt-4 text-white/80">{{ $body }}</p>
                @if (! empty($values))
                    <div class="mt-6 flex flex-wrap gap-2">
                        @foreach ($values as $value)
                            <span class="rounded-full bg-white/10 px-3 py-1 text-sm font-medium ring-1 ring-white/15">{{ is_array($value) ? ($value['value'] ?? '') : $value }}</span>
                        @endforeach
                    </div>
                @endif
                @if ($closing)
                    <p class="mt-5 text-brand-300 font-semibold">{{ $closing }}</p>
                @endif
            </div>
            <div class="grid grid-cols-2 gap-5">
                @foreach ($stats as $stat)
                    <div class="rounded-2xl bg-white/5 p-6 ring-1 ring-white/10">
                        <p class="text-xs font-semibold uppercase tracking-wide text-white/60">{{ $stat['label'] ?? '' }}</p>
                        <p class="mt-1 text-3xl font-extrabold text-brand-400">{{ $stat['value'] ?? '' }}</p>
                        <p class="mt-1 text-sm text-white/70">{{ $stat['caption'] ?? '' }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
