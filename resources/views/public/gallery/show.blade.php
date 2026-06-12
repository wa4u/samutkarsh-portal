@extends('layouts.public')

@section('title', $gallery->title . ' — Gallery')

@section('content')
    <div class="mx-auto max-w-5xl px-4 py-12">
        <a href="{{ route('public.gallery.index') }}" class="text-sm text-indigo-600 hover:underline">&larr; All albums</a>
        <h1 class="mt-3 text-2xl font-bold text-slate-900">{{ $gallery->title }}</h1>
        <p class="mt-1 text-sm text-slate-500">{{ $gallery->center?->name ?? 'Samutkarsh' }}</p>
        @if ($gallery->description)
            <p class="mt-3 text-slate-600 max-w-2xl">{{ $gallery->description }}</p>
        @endif

        <div class="mt-8 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
            @foreach ($gallery->items as $item)
                @if ($item->type === 'image')
                    <button type="button"
                            class="js-lightbox group relative aspect-square overflow-hidden rounded-lg bg-slate-100 ring-1 ring-slate-200"
                            data-type="image" data-src="{{ $item->displayUrl() }}" data-caption="{{ $item->caption }}">
                        <img src="{{ $item->thumbUrl() }}" alt="{{ $item->caption }}" loading="lazy"
                             class="h-full w-full object-cover group-hover:scale-105 transition duration-300">
                    </button>
                @else
                    <button type="button"
                            class="js-lightbox group relative aspect-square overflow-hidden rounded-lg bg-slate-900 ring-1 ring-slate-200"
                            data-type="youtube" data-src="{{ $item->youtubeEmbedUrl() }}" data-caption="{{ $item->caption }}">
                        <img src="{{ $item->youtubeThumbUrl() }}" alt="{{ $item->caption }}" loading="lazy"
                             class="h-full w-full object-cover opacity-80 group-hover:opacity-100 transition">
                        <span class="absolute inset-0 flex items-center justify-center">
                            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-white/90 shadow">
                                <svg class="h-6 w-6 text-red-600 translate-x-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            </span>
                        </span>
                    </button>
                @endif
            @endforeach
        </div>
    </div>

    {{-- Lightbox --}}
    <div id="lightbox" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/80 p-4">
        <button id="lightbox-close" class="absolute top-4 right-4 text-white/80 hover:text-white text-3xl leading-none">&times;</button>
        <div class="max-w-4xl w-full">
            <div id="lightbox-body" class="flex items-center justify-center"></div>
            <p id="lightbox-caption" class="mt-3 text-center text-sm text-white/80"></p>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const box = document.getElementById('lightbox');
            const body = document.getElementById('lightbox-body');
            const cap = document.getElementById('lightbox-caption');

            function open(type, src, caption) {
                body.innerHTML = type === 'youtube'
                    ? `<div class="relative w-full" style="padding-top:56.25%"><iframe class="absolute inset-0 h-full w-full rounded-lg" src="${src}?autoplay=1" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe></div>`
                    : `<img src="${src}" class="max-h-[80vh] mx-auto rounded-lg" alt="">`;
                cap.textContent = caption || '';
                box.classList.remove('hidden');
                box.classList.add('flex');
            }
            function close() {
                box.classList.add('hidden');
                box.classList.remove('flex');
                body.innerHTML = ''; // stop video playback
            }

            document.querySelectorAll('.js-lightbox').forEach(el =>
                el.addEventListener('click', () => open(el.dataset.type, el.dataset.src, el.dataset.caption)));
            document.getElementById('lightbox-close').addEventListener('click', close);
            box.addEventListener('click', e => { if (e.target === box) close(); });
            document.addEventListener('keydown', e => { if (e.key === 'Escape') close(); });
        })();
    </script>
@endpush
