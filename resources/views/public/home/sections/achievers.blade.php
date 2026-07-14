@php
    use Illuminate\Support\Str;
    $c = $c ?? [];
    $heading = $c['heading'] ?? 'Our Achievers';
    $intro   = $c['intro'] ?? 'Students who went on to do extraordinary things after their Samutkarsh journey.';
    $items   = $achievers ?? collect();
@endphp

@if ($items->isNotEmpty())
    <section class="bg-white">
        <div class="mx-auto max-w-6xl px-4 py-20">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-3xl font-extrabold tracking-tight text-slate-900">{{ $heading }}</h2>
                @if ($intro)<p class="mt-3 text-slate-600">{{ $intro }}</p>@endif
            </div>

            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($items as $a)
                    <a href="{{ route('public.achievers.show', $a->slug) }}"
                       class="group flex flex-col overflow-hidden rounded-2xl bg-white ring-1 ring-slate-200 shadow-sm transition hover:shadow-md">
                        <div class="aspect-[4/5] overflow-hidden bg-slate-100">
                            @if ($a->photoUrl())
                                <img src="{{ $a->photoUrl() }}" alt="{{ $a->name }}" loading="lazy"
                                     class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                            @else
                                <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-brand-500 to-brand-700 text-5xl font-extrabold text-white">
                                    {{ Str::of($a->name)->trim()->substr(0, 1)->upper() }}
                                </div>
                            @endif
                        </div>
                        <div class="flex flex-1 flex-col p-4">
                            <h3 class="font-bold text-slate-900 group-hover:text-brand-700">{{ $a->name }}</h3>
                            <p class="mt-1 text-sm font-semibold text-brand-600">{{ $a->headline }}</p>
                            @if ($a->meta())
                                <p class="mt-1 text-xs text-slate-500">{{ $a->meta() }}</p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-10 text-center">
                <a href="{{ route('public.achievers.index') }}" class="text-sm font-semibold text-brand-600 hover:underline">See all achievers &rarr;</a>
            </div>
        </div>
    </section>
@endif
