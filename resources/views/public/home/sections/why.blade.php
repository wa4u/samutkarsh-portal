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
