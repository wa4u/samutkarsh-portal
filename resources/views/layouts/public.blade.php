<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-full bg-slate-50">
@php
    use App\Models\Setting;
    use Illuminate\Support\Str;
    // Logo: an uploaded file (stored path) or a pasted URL — resolve both to a usable src.
    $logoRaw  = Setting::get('site.logo_url');
    $logo     = $logoRaw ? (Str::startsWith($logoRaw, ['http://', 'https://', '/']) ? $logoRaw : \Illuminate\Support\Facades\Storage::disk('public')->url($logoRaw)) : null;
    $logoAbs  = $logo ? (Str::startsWith($logo, ['http://', 'https://']) ? $logo : url($logo)) : null;
    $tagline  = Setting::get('site.tagline', 'Nation Building through IAS');
    $phone    = Setting::get('contact.phone_hubballi');
    $email    = Setting::get('contact.email');
    $whatsapp = preg_replace('/\D+/', '', (string) Setting::get('contact.whatsapp'));
    $regOpen  = (bool) config('admissions.registration_open');
    // Admin can hide the Register CTA even while admissions are open.
    $showRegister = $regOpen && filter_var(Setting::get('site.show_register', '1'), FILTER_VALIDATE_BOOLEAN);
    $footerMenu = $footerMenu ?? collect();
    $socials  = array_values(array_filter([
        Setting::get('social.facebook'), Setting::get('social.instagram'), Setting::get('social.youtube'),
    ]));
    $seoDesc  = trim(strip_tags((string) Setting::get('site.hero_subtitle', 'Nation Building through IAS — civil services coaching from school foundation to UPSC/KPSC, across Karnataka.')));
    // Built in PHP (not @json) so the @-prefixed schema.org keys aren't parsed as Blade directives.
    // Language switcher / hreflang: build raw URLs (bypassing the locale path
    // formatter) for the current page in each language.
    $curLocale = app()->getLocale();
    $bare = '/' . ltrim(request()->path(), '/');
    $bare = preg_replace('#^/(hi|kn)(?=/|$)#', '', $bare);
    if ($bare === '' || $bare === false) { $bare = '/'; }
    $root = request()->getSchemeAndHttpHost();
    $langUrls = [
        'en' => $root . $bare,
        'hi' => $root . '/hi' . ($bare === '/' ? '' : $bare),
        'kn' => $root . '/kn' . ($bare === '/' ? '' : $bare),
    ];
    $langLabels = ['en' => 'English', 'hi' => 'हिन्दी', 'kn' => 'ಕನ್ನಡ'];

    $orgJsonLd = json_encode(array_filter([
        '@context'    => 'https://schema.org',
        '@type'       => 'EducationalOrganization',
        'name'        => 'Samutkarsh IAS Academy',
        'url'         => url('/'),
        'description' => $seoDesc,
        'logo'        => $logoAbs,
        'email'       => $email ?: null,
        'telephone'   => $phone ?: null,
        'sameAs'      => $socials ?: null,
    ]), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
@endphp
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Google Analytics (gtag.js) — Measurement ID is admin-editable: Settings → site.ga_id (blank disables). --}}
    @php($gaId = trim((string) Setting::get('site.ga_id', 'G-TDYG1WK6KY')))
    @if ($gaId)
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ urlencode($gaId) }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', @json($gaId));
        </script>
    @endif

    <title>@yield('title', 'Samutkarsh IAS Academy')</title>

    {{-- Favicons --}}
    <link rel="icon" href="{{ asset('favicons/favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicons/favicon-16x16.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicons/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="48x48" href="{{ asset('favicons/favicon-48x48.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicons/apple-touch-icon-180x180.png') }}">
    <link rel="manifest" href="{{ asset('favicons/site.webmanifest') }}">
    <meta name="theme-color" content="#ea580c">


    {{-- SEO --}}
    <meta name="description" content="@yield('meta_description', $seoDesc)">
    <link rel="canonical" href="{{ url()->current() }}">
    @foreach ($langUrls as $lc => $href)
        <link rel="alternate" hreflang="{{ $lc }}" href="{{ $href }}">
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ $langUrls['en'] }}">
    <meta property="og:site_name" content="Samutkarsh IAS Academy">
    <meta property="og:type" content="website">
    <meta property="og:title" content="@yield('og_title', 'Samutkarsh IAS Academy')">
    <meta property="og:description" content="@yield('meta_description', $seoDesc)">
    <meta property="og:url" content="{{ url()->current() }}">
    @hasSection('og_image')
        <meta property="og:image" content="@yield('og_image')">
    @elseif ($logoAbs)
        <meta property="og:image" content="{{ $logoAbs }}">
    @endif
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('og_title', 'Samutkarsh IAS Academy')">
    <meta name="twitter:description" content="@yield('meta_description', $seoDesc)">

    {{-- Organization structured data --}}
    <script type="application/ld+json">{!! $orgJsonLd !!}</script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-full flex flex-col text-slate-800 antialiased">
    {{-- Utility bar --}}
    <div class="bg-ink-900 text-white/90 text-xs">
        <div class="mx-auto max-w-6xl px-4 h-9 flex items-center justify-between gap-4">
            <div class="flex items-center gap-4 min-w-0">
                @if ($phone)
                    <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}" class="inline-flex items-center gap-1.5 hover:text-white truncate">
                        <svg class="h-3.5 w-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M2 3.5A1.5 1.5 0 0 1 3.5 2h1.6a1.5 1.5 0 0 1 1.46 1.14l.55 2.2a1.5 1.5 0 0 1-.4 1.43l-.9.9a11 11 0 0 0 4.9 4.9l.9-.9a1.5 1.5 0 0 1 1.43-.4l2.2.55A1.5 1.5 0 0 1 18 14.9v1.6a1.5 1.5 0 0 1-1.5 1.5A14.5 14.5 0 0 1 2 3.5Z"/></svg>
                        <span class="truncate">{{ $phone }}</span>
                    </a>
                @endif
                @if ($email)
                    <a href="mailto:{{ $email }}" class="hidden sm:inline-flex items-center gap-1.5 hover:text-white truncate">
                        <svg class="h-3.5 w-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M3 4h14a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1Zm.4 2 6.6 4.4L16.6 6H3.4Z"/></svg>
                        <span class="truncate">{{ $email }}</span>
                    </a>
                @endif
            </div>
            <div class="flex items-center gap-3 shrink-0">
                {{-- Language switcher --}}
                <div class="relative group">
                    <button class="inline-flex items-center gap-1 hover:text-white" aria-label="Change language">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m3.5 13 4.5-9 4.5 9M5 8c0 4 2.5 7 6 8M9 12c2 2 4 3 6 3"/></svg>
                        <span>{{ $langLabels[$curLocale] ?? 'English' }}</span>
                        <svg class="h-3 w-3 opacity-70" viewBox="0 0 20 20" fill="currentColor"><path d="M5.5 7.5 10 12l4.5-4.5z"/></svg>
                    </button>
                    <div class="absolute right-0 top-full hidden group-hover:block pt-2 w-36 z-50">
                        <div class="rounded-lg bg-white py-1 shadow-xl ring-1 ring-slate-200 text-slate-700">
                            @foreach ($langLabels as $lc => $label)
                                <a href="{{ $langUrls[$lc] }}"
                                   class="block px-3 py-2 text-sm hover:bg-brand-50 hover:text-brand-700 {{ $curLocale === $lc ? 'font-bold text-brand-700' : '' }}">{{ $label }}</a>
                            @endforeach
                        </div>
                    </div>
                </div>
                <span class="text-white/30">|</span>
                <a href="{{ route('public.result.form') }}" class="hover:text-white">Check result</a>
            </div>
        </div>
    </div>

    {{-- Main header --}}
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-sm">
        <div class="mx-auto max-w-6xl px-4 h-20 flex items-center justify-between gap-4">
            <a href="{{ route('public.home') }}" class="flex items-center gap-3 shrink-0">
                @if ($logo)
                    <img src="{{ $logo }}" alt="Samutkarsh IAS Academy" class="h-12 w-auto">
                @else
                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 text-white text-lg font-extrabold shadow-sm">S</span>
                @endif
                <span class="leading-tight">
                    <span class="block font-extrabold text-slate-900 tracking-tight">Samutkarsh<span class="text-brand-600"> IAS</span></span>
                    <span class="block text-[11px] font-medium uppercase tracking-wide text-slate-500">{{ $tagline }}</span>
                </span>
            </a>

            @php
                $fallback = collect([
                    (object) ['label' => 'Home', 'href' => route('public.home')],
                    (object) ['label' => 'Activities', 'href' => route('public.activities')],
                    (object) ['label' => 'Blog', 'href' => route('public.blog.index')],
                    (object) ['label' => 'Gallery', 'href' => route('public.gallery.index')],
                ]);
            @endphp

            {{-- Desktop nav (xl+, so long labels don't wrap) --}}
            <nav class="hidden xl:flex items-center gap-0.5 text-sm font-semibold">
                @forelse ($headerMenu ?? [] as $item)
                    @if ($item->hasChildren())
                        <div class="relative group">
                            <button class="whitespace-nowrap px-3 py-2 rounded-md text-slate-700 hover:text-brand-700 hover:bg-brand-50 inline-flex items-center gap-1">
                                {{ $item->label }}
                                <svg class="h-3 w-3 opacity-60 transition-transform group-hover:rotate-180" viewBox="0 0 20 20" fill="currentColor"><path d="M5.5 7.5 10 12l4.5-4.5z"/></svg>
                            </button>
                            {{-- Simple dropdown (clean, WWF-style) --}}
                            <div class="absolute left-0 top-full hidden group-hover:block pt-2 z-50">
                                <div class="w-72 rounded-xl bg-white py-2 shadow-xl ring-1 ring-slate-200">
                                    @foreach ($item->children as $child)
                                        <a href="{{ $child->url() }}" @if ($child->target_blank) target="_blank" rel="noopener" @endif
                                           class="block px-4 py-2.5 hover:bg-brand-50">
                                            <span class="block text-slate-700 group-hover:text-slate-700 hover:!text-brand-700">{{ $child->label }}</span>
                                            @if ($child->description)
                                                <span class="block text-xs font-normal text-slate-400">{{ $child->description }}</span>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ $item->url() }}" @if ($item->target_blank) target="_blank" rel="noopener" @endif
                           class="whitespace-nowrap px-3 py-2 rounded-md text-slate-700 hover:text-brand-700 hover:bg-brand-50">{{ $item->label }}</a>
                    @endif
                @empty
                    @foreach ($fallback as $item)
                        <a href="{{ $item->href }}" class="whitespace-nowrap px-3 py-2 rounded-md text-slate-700 hover:text-brand-700 hover:bg-brand-50">{{ $item->label }}</a>
                    @endforeach
                @endforelse
            </nav>

            <div class="flex items-center gap-2 shrink-0">
                @if ($showRegister)
                    <a href="{{ route('public.register.create') }}"
                       class="hidden sm:inline-flex items-center rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-brand-700 transition">
                        Register now
                    </a>
                @endif
                <button id="mobile-menu-btn" class="xl:hidden p-2 text-slate-700" aria-label="Menu" aria-expanded="false">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5"/></svg>
                </button>
            </div>
        </div>

        {{-- Mobile nav --}}
        <nav id="mobile-menu" class="xl:hidden hidden border-t border-slate-200 px-4 py-3 space-y-1 text-sm font-medium">
            @forelse ($headerMenu ?? [] as $item)
                @if ($item->hasChildren())
                    <div class="py-1">
                        <p class="px-2 py-1 font-bold text-slate-900">{{ $item->label }}</p>
                        @foreach ($item->children as $child)
                            <a href="{{ $child->url() }}" @if ($child->target_blank) target="_blank" rel="noopener" @endif
                               class="block pl-5 pr-2 py-2 text-slate-600 hover:text-brand-700">{{ $child->label }}</a>
                        @endforeach
                    </div>
                @else
                    <a href="{{ $item->url() }}" @if ($item->target_blank) target="_blank" rel="noopener" @endif
                       class="block px-2 py-2 text-slate-700 hover:text-brand-700">{{ $item->label }}</a>
                @endif
            @empty
                @foreach ($fallback as $item)
                    <a href="{{ $item->href }}" class="block px-2 py-2 text-slate-700 hover:text-brand-700">{{ $item->label }}</a>
                @endforeach
            @endforelse
            @if ($showRegister)
                <a href="{{ route('public.register.create') }}" class="mt-2 block rounded-lg bg-brand-600 px-3 py-2.5 text-center font-bold text-white">Register now</a>
            @endif
        </nav>
    </header>

    <main class="flex-1">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-ink-900 text-slate-300">
        <div class="mx-auto max-w-6xl px-4 py-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
            <div class="sm:col-span-2 lg:col-span-1">
                <div class="flex items-center gap-3">
                    @if ($logo)
                        <img src="{{ $logo }}" alt="Samutkarsh IAS Academy" class="h-11 w-auto bg-white/95 rounded-lg p-1">
                    @else
                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 text-white text-lg font-extrabold">S</span>
                    @endif
                    <span class="font-extrabold text-white">Samutkarsh<span class="text-brand-400"> IAS</span></span>
                </div>
                <p class="mt-4 text-sm text-slate-400">{{ $tagline }}. A socially committed Trust building Centres of Excellence for civil services across Karnataka.</p>
            </div>

            <div>
                <h3 class="text-sm font-bold uppercase tracking-wide text-white">Explore</h3>
                <ul class="mt-4 space-y-2 text-sm">
                    @forelse ($footerMenu as $item)
                        <li><a href="{{ $item->url() }}" @if ($item->target_blank) target="_blank" rel="noopener" @endif class="hover:text-white">{{ $item->label }}</a></li>
                    @empty
                        <li><a href="{{ route('public.home') }}" class="hover:text-white">Home</a></li>
                        <li><a href="{{ route('public.activities') }}" class="hover:text-white">Activities</a></li>
                        <li><a href="{{ route('public.blog.index') }}" class="hover:text-white">Blog &amp; Articles</a></li>
                        <li><a href="{{ route('public.gallery.index') }}" class="hover:text-white">Gallery</a></li>
                        <li><a href="{{ route('public.contact') }}" class="hover:text-white">Contact &amp; Admissions</a></li>
                    @endforelse
                </ul>
            </div>

            <div>
                <h3 class="text-sm font-bold uppercase tracking-wide text-white">Get in touch</h3>
                <ul class="mt-4 space-y-2 text-sm">
                    @if ($phone)<li><a href="tel:{{ preg_replace('/\s+/', '', $phone) }}" class="hover:text-white">{{ $phone }}</a></li>@endif
                    @if ($email)<li><a href="mailto:{{ $email }}" class="hover:text-white break-all">{{ $email }}</a></li>@endif
                    @if ($addr = Setting::get('contact.address_hubballi'))<li class="text-slate-400">{{ $addr }}</li>@endif
                </ul>
            </div>

            <div>
                <h3 class="text-sm font-bold uppercase tracking-wide text-white">Follow</h3>
                <div class="mt-4 flex items-center gap-3">
                    @foreach (['facebook' => 'M13.5 9H15V6.5h-1.7c-1.8 0-2.8 1-2.8 2.9V11H9v2.5h1.5V19H13v-5.5h1.8L15 11h-2V9.6c0-.4.2-.6.5-.6Z', 'instagram' => 'M10 7.2A2.8 2.8 0 1 0 10 12.8 2.8 2.8 0 0 0 10 7.2Zm4.3-.5a.7.7 0 1 1-1.4 0 .7.7 0 0 1 1.4 0ZM6 5h8a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z', 'youtube' => 'M16.5 7.2a1.7 1.7 0 0 0-1.2-1.2C14.2 5.7 10 5.7 10 5.7s-4.2 0-5.3.3A1.7 1.7 0 0 0 3.5 7.2 17 17 0 0 0 3.3 10c0 1 .1 1.9.2 2.8a1.7 1.7 0 0 0 1.2 1.2c1.1.3 5.3.3 5.3.3s4.2 0 5.3-.3a1.7 1.7 0 0 0 1.2-1.2c.1-.9.2-1.8.2-2.8s-.1-1.9-.2-2.8ZM8.8 12V8l3.4 2-3.4 2Z'] as $net => $path)
                        @if ($link = Setting::get("social.$net"))
                            <a href="{{ $link }}" target="_blank" rel="noopener" aria-label="{{ ucfirst($net) }}"
                               class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-white/10 hover:bg-brand-600 transition">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path d="{{ $path }}"/></svg>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
        <div class="border-t border-white/10">
            <div class="mx-auto max-w-6xl px-4 py-5 text-xs text-slate-400 flex flex-col sm:flex-row justify-between gap-2">
                <span>&copy; {{ date('Y') }} {{ Setting::get('footer.copyright', 'Samutkarsh IAS Academy. All rights reserved.') }}</span>
                <span>{{ Setting::get('footer.note', 'Admissions ' . config('admissions.academic_year')) }}</span>
            </div>
        </div>
    </footer>

    {{-- Floating WhatsApp button --}}
    @if ($whatsapp)
        <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener" aria-label="Chat on WhatsApp"
           class="wa-float fixed bottom-5 right-5 z-50 inline-flex h-14 w-14 items-center justify-center rounded-full bg-[#25D366] text-white shadow-lg hover:scale-105 transition">
            <svg class="h-8 w-8" fill="currentColor" viewBox="0 0 24 24"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38c1.45.79 3.08 1.21 4.79 1.21h.01c5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2Zm0 18.13h-.01c-1.52 0-3.01-.41-4.31-1.18l-.31-.18-3.2.84.85-3.12-.2-.32a8.13 8.13 0 0 1-1.25-4.32c0-4.54 3.7-8.23 8.24-8.23 2.2 0 4.27.86 5.82 2.42a8.18 8.18 0 0 1 2.41 5.82c0 4.54-3.69 8.23-8.23 8.23Zm4.52-6.16c-.25-.12-1.47-.72-1.69-.81-.23-.08-.39-.12-.56.13-.16.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.12-1.05-.39-1.99-1.23-.74-.66-1.23-1.47-1.38-1.72-.14-.25-.01-.38.11-.51.11-.11.25-.29.37-.43.13-.14.17-.25.25-.41.08-.17.04-.31-.02-.43-.06-.12-.56-1.34-.76-1.84-.2-.48-.4-.42-.56-.43h-.48c-.17 0-.43.06-.66.31-.23.25-.86.85-.86 2.07 0 1.22.89 2.4 1.01 2.56.12.17 1.75 2.67 4.23 3.74.59.26 1.05.41 1.41.52.59.19 1.13.16 1.56.1.48-.07 1.47-.6 1.68-1.18.21-.58.21-1.07.14-1.18-.06-.11-.22-.17-.47-.29Z"/></svg>
        </a>
    @endif

    <script>
        const btn = document.getElementById('mobile-menu-btn');
        btn?.addEventListener('click', () => {
            const menu = document.getElementById('mobile-menu');
            const open = menu?.classList.toggle('hidden') === false;
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    </script>

    @stack('scripts')
</body>
</html>
