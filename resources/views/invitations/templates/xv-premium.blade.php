@php
    $config = $modulos['config'] ?? [];
    $colores = $config['colores'] ?? [];
    $tipografias = $config['tipografias'] ?? [];
    $flags = $config['modulos'] ?? [];
    $bienvenida = $modulos['bienvenida'] ?? [];
    $musica = $modulos['musica'] ?? [];
    $isPostEvent = $invitation->is_post_event;
    $guestToken = $guest?->qr_code_token ?? '';
    $heroImage = $bienvenida['imagen_hero'] ?? null;
    $hasHeroImage = !empty($heroImage);
    $postEventVisibleModules = ['musica', 'fotomural', 'galeria', 'hashtag', 'post_evento'];
    $moduleVisible = function (string $key) use ($flags, $isPostEvent, $postEventVisibleModules): bool {
        if ($isPostEvent) {
            return in_array($key, $postEventVisibleModules, true) && (bool) ($flags[$key] ?? false);
        }

        return (bool) ($flags[$key] ?? false);
    };
    $navItems = [];
    if (!$isPostEvent && $moduleVisible('cuenta_regresiva')) {
        $navItems[] = ['id' => 'cuenta-regresiva', 'label' => 'Cuenta regresiva'];
    }
    foreach ([
        'video' => 'Video',
        'galeria' => 'Galería',
        'itinerario' => 'Itinerario',
        'dress_code' => 'Dress code',
        'destacados' => 'Cortejo',
        'ubicacion' => 'Ubicación',
        'hashtag' => 'Hashtag',
        'encuestas' => 'Encuestas',
        'playlist' => 'Playlist',
        'regalos' => 'Regalos',
        'rsvp' => 'RSVP',
        'fotomural' => 'Fotomural',
        'post_evento' => 'Post evento',
    ] as $moduleKey => $label) {
        if ($moduleVisible($moduleKey)) {
            $navItems[] = ['id' => str_replace('_', '-', $moduleKey), 'label' => $label];
        }
    }
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $bienvenida['nombre_quinceanera'] ?? $invitation->title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family={{ urlencode($tipografias['titulos'] ?? 'Playfair Display') }}:wght@400;600;700&family={{ urlencode($tipografias['cuerpo'] ?? 'Montserrat') }}:wght@300;400;500;600&family={{ urlencode($tipografias['script'] ?? 'Great Vibes') }}&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Cormorant+Garamond:wght@400;600;700&family=Cinzel:wght@400;600;700&family=Libre+Baskerville:wght@400;700&family=Bodoni+Moda:wght@400;600;700&family=Prata&family=Lora:wght@400;500;600;700&family=Merriweather:wght@300;400;700&family=Montserrat:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&family=Lato:wght@300;400;700&family=Nunito+Sans:wght@300;400;600;700&family=Source+Sans+3:wght@300;400;600;700&family=Poppins:wght@300;400;500;600;700&family=Raleway:wght@300;400;500;600;700&family=Open+Sans:wght@300;400;600;700&family=Great+Vibes&family=Parisienne&family=Alex+Brush&family=Dancing+Script:wght@400;700&family=Sacramento&family=Allura&family=Tangerine:wght@400;700&family=Petit+Formal+Script&display=swap" rel="stylesheet">
    <style>
        html { scroll-behavior: smooth; }
        :root {
            --primary-color: {{ $colores['primary'] ?? '#C9A96E' }};
            --secondary-color: {{ $colores['secondary'] ?? '#2C1810' }};
            --accent-color: {{ $colores['accent'] ?? '#F5E6D3' }};
            --text-color: {{ $colores['text'] ?? '#1A1A1A' }};
            --bg-color: {{ $colores['background'] ?? '#FFFAF5' }};
            --surface-color: color-mix(in srgb, var(--accent-color) 32%, var(--bg-color));
            --surface-soft: color-mix(in srgb, var(--accent-color) 18%, var(--bg-color));
            --font-titles: '{{ $tipografias['titulos'] ?? 'Playfair Display' }}', serif;
            --font-body: '{{ $tipografias['cuerpo'] ?? 'Montserrat' }}', sans-serif;
            --font-script: '{{ $tipografias['script'] ?? 'Great Vibes' }}', cursive;
        }
        body { font-family: var(--font-body); color: var(--text-color); background: var(--bg-color); }
        .font-title { font-family: var(--font-titles); }
        .font-script { font-family: var(--font-script); }
        .text-primary { color: var(--primary-color); }
        .text-secondary { color: var(--secondary-color); }
        .bg-primary { background-color: var(--primary-color); }
        .bg-secondary { background-color: var(--secondary-color); }
        .border-primary { border-color: var(--primary-color); }
        .border-secondary { border-color: var(--secondary-color); }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
    </style>
</head>
<body class="overflow-x-hidden pb-28" x-data="invitationApp()" x-init="init()">

    <div class="fixed top-4 right-4 z-50" x-data="{ open: false }" @keydown.escape.window="open = false">
        <button type="button"
            @click="open = !open"
            class="inline-flex items-center gap-2 rounded-full border border-white/35 bg-white/80 px-4 py-2.5 text-xs font-semibold uppercase tracking-[0.24em] text-secondary shadow-lg backdrop-blur-xl transition hover:bg-white">
            <span>Menú</span>
            <svg class="h-3.5 w-3.5 transition-transform duration-200" :class="open ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M6 9l6 6 6-6" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
        </button>

        <div x-show="open"
            x-cloak
            @click.outside="open = false"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 translate-y-2 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-2 scale-95"
            class="absolute right-0 mt-3 w-64 overflow-hidden rounded-3xl border border-white/40 bg-white/95 p-2 shadow-2xl backdrop-blur-xl">
            <a href="#inicio" @click="open = false"
                class="flex items-center justify-between rounded-2xl px-4 py-3 text-sm font-medium text-secondary transition hover:bg-primary/5">
                <span>Inicio</span>
            </a>
            @foreach($navItems as $item)
                <a href="#{{ $item['id'] }}" @click="open = false"
                    class="flex items-center justify-between rounded-2xl px-4 py-3 text-sm font-medium text-secondary transition hover:bg-primary/5">
                    <span>{{ $item['label'] }}</span>
                    <span class="text-[10px] uppercase tracking-[0.2em] text-primary/70">Ir</span>
                </a>
            @endforeach
        </div>
    </div>

    <div class="fixed inset-0 pointer-events-none z-0 overflow-hidden" aria-hidden="true">
        @php $pSizes = [4,5,6,7,5,4,6,5,4,6,5,7,4,5,6,4,7,5,6,4]; @endphp
        @for($i = 0; $i < 20; $i++)
            <span class="particle-dot absolute rounded-full bg-primary"
                style="left:{{ rand(2, 98) }}%;top:{{ rand(2, 98) }}%;width:{{ $pSizes[$i] }}px;height:{{ $pSizes[$i] }}px;animation-delay:{{ round($i * 0.38, 1) }}s;animation-duration:{{ 5 + ($i % 4) }}s"></span>
        @endfor
    </div>

    @include('invitations.partials.music-player', ['musica' => $musica, 'flags' => array_merge($flags, ['musica' => $moduleVisible('musica')])])

    {{-- HERO --}}
    <header id="inicio" class="relative min-h-[100dvh] flex flex-col items-center justify-center text-center overflow-hidden">
        <div class="hero-bg {{ $hasHeroImage ? '' : 'hero-no-image' }}">
            @if($hasHeroImage)
                <img src="{{ $heroImage }}" alt="" class="hero-ken-burns" loading="eager">
                <div class="hero-overlay"></div>
            @endif
        </div>

        <div class="hero-content px-6 py-20 sm:px-8 w-full max-w-lg {{ $hasHeroImage ? 'hero-text-light' : '' }}">
            @if($isPostEvent && $moduleVisible('post_evento'))
                <p class="text-[10px] uppercase tracking-[0.35em] mb-5 animate-fade-up {{ $hasHeroImage ? 'text-white/75' : 'text-secondary' }}">
                    {{ $bienvenida['subtitulo'] ?? 'Mis XV Años' }}
                </p>
                <h1 class="font-script text-6xl sm:text-[5.5rem] leading-none mb-6 animate-fade-up animate-fade-up-delay-1 {{ $hasHeroImage ? 'text-white drop-shadow-lg' : 'text-primary' }}">
                    {{ $bienvenida['nombre_quinceanera'] ?? 'Quinceañera' }}
                </h1>
                @if(!empty($bienvenida['mensaje']))
                    <p class="font-title text-base sm:text-lg max-w-sm mx-auto leading-relaxed animate-fade-up animate-fade-up-delay-2 {{ $hasHeroImage ? 'text-white/85' : 'text-secondary/90' }}">
                        {{ $bienvenida['mensaje'] }}
                    </p>
                @endif
                <div class="mt-8 animate-fade-up animate-fade-up-delay-3">
                    <div class="section-ornament {{ $hasHeroImage ? 'bg-white/40' : '' }}" style="{{ $hasHeroImage ? 'background: linear-gradient(90deg, transparent, rgba(255,255,255,0.5), transparent)' : '' }}"></div>
                    <p class="mt-4 text-xs tracking-[0.25em] uppercase py-3 px-6 inline-block {{ $hasHeroImage ? 'text-white/90 border border-white/25 rounded-full' : 'border-t border-b border-primary/25' }}">
                        {{ $bienvenida['fecha_texto'] ?? $invitation->event_date->format('d \d\e F, Y') }}
                    </p>
                </div>
            @else
                <p class="text-[10px] uppercase tracking-[0.35em] mb-5 animate-fade-up {{ $hasHeroImage ? 'text-white/75' : 'text-secondary' }}">
                    {{ $bienvenida['subtitulo'] ?? 'Mis XV Años' }}
                </p>
                <h1 class="font-script text-6xl sm:text-[5.5rem] leading-none mb-6 animate-fade-up animate-fade-up-delay-1 {{ $hasHeroImage ? 'text-white drop-shadow-lg' : 'text-primary' }}">
                    {{ $bienvenida['nombre_quinceanera'] ?? 'Quinceañera' }}
                </h1>
                @if(!empty($bienvenida['mensaje']))
                    <p class="font-title text-base sm:text-lg max-w-sm mx-auto leading-relaxed animate-fade-up animate-fade-up-delay-2 {{ $hasHeroImage ? 'text-white/85' : 'text-secondary/90' }}">
                        {{ $bienvenida['mensaje'] }}
                    </p>
                @endif
                <div class="mt-8 animate-fade-up animate-fade-up-delay-3">
                    <div class="section-ornament {{ $hasHeroImage ? 'bg-white/40' : '' }}" style="{{ $hasHeroImage ? 'background: linear-gradient(90deg, transparent, rgba(255,255,255,0.5), transparent)' : '' }}"></div>
                    <p class="mt-4 text-xs tracking-[0.25em] uppercase py-3 px-6 inline-block {{ $hasHeroImage ? 'text-white/90 border border-white/25 rounded-full' : 'border-t border-b border-primary/25' }}">
                        {{ $bienvenida['fecha_texto'] ?? $invitation->event_date->format('d \d\e F, Y') }}
                    </p>
                </div>
            @endif

            @if($guest)
                <div class="mt-10 mx-auto px-6 py-5 rounded-2xl glass-card max-w-sm w-full animate-fade-up animate-fade-up-delay-4 {{ $hasHeroImage ? '!bg-white/15 !border-white/25 !text-white backdrop-blur-md' : '' }}">
                    <p class="text-[10px] uppercase tracking-widest opacity-60">Invitación personal</p>
                    <p class="font-title text-xl mt-2">{{ $guest->name }}</p>
                    @if($guest->status === 'pending')
                        <p class="text-sm mt-3 {{ $hasHeroImage ? 'text-white/80' : 'text-primary' }}">
                            Tienes <strong>{{ $guest->passes_allocated }}</strong> {{ $guest->passes_allocated === 1 ? 'pase disponible' : 'pases disponibles' }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="absolute bottom-8 left-1/2 -translate-x-1/2 hero-scroll {{ $hasHeroImage ? 'text-white/50' : 'text-primary/40' }}">
            @include('invitations.partials.icon', ['name' => 'chevron-down', 'class' => 'w-6 h-6', 'animated' => false])
        </div>
    </header>

    @if($isPostEvent && $moduleVisible('post_evento') && !empty($bienvenida['mensaje_post_evento']))
        <section class="invitation-section reveal pt-8 pb-2">
            <div class="section-inner-wide text-center">
                <p class="font-title text-3xl sm:text-4xl leading-snug max-w-md mx-auto text-primary {{ $hasHeroImage ? '!text-secondary' : '' }}">
                    {{ $bienvenida['mensaje_post_evento'] }}
                </p>
            </div>
        </section>
    @endif

    @if($moduleVisible('cuenta_regresiva') && !$isPostEvent)
        @include('invitations.partials.countdown', [
            'eventDate' => $invitation->event_date->toIso8601String(),
            'calendarUrl' => $calendarUrl,
            'agendar' => $moduleVisible('agendar'),
        ])
    @endif

    @if(!$moduleVisible('cuenta_regresiva') && $moduleVisible('agendar') && !$isPostEvent)
        <section class="invitation-section reveal py-6">
            <div class="section-inner text-center">
                <button type="button" onclick="openCalendar('{{ $calendarUrl }}')"
                    class="inline-flex items-center gap-2 px-6 py-3 rounded-full inv-card text-sm font-medium text-primary active:scale-[0.98] transition-transform">
                    @include('invitations.partials.icon', ['name' => 'calendar', 'class' => 'w-4 h-4', 'animated' => false])
                    Agendar en Google Calendar
                </button>
            </div>
        </section>
    @endif

    @if($moduleVisible('video'))
        @include('invitations.partials.video', ['video' => $modulos['video']])
    @endif

    @if($moduleVisible('galeria'))
        @include('invitations.partials.gallery-stack', ['galeria' => $modulos['galeria']])
    @endif

    @if($moduleVisible('itinerario'))
        @include('invitations.partials.itinerary', ['itinerario' => $modulos['itinerario'] ?? []])
    @endif

    @if($moduleVisible('dress_code'))
        @include('invitations.partials.dress-code', ['dressCode' => $modulos['dress_code'] ?? []])
    @endif

    @if($moduleVisible('destacados'))
        @include('invitations.partials.destacados', ['destacados' => $modulos['destacados'] ?? []])
    @endif

    @if($moduleVisible('ubicacion'))
        @include('invitations.partials.location', [
            'ubicacion' => $modulos['ubicacion'] ?? [],
            'agendar' => $moduleVisible('agendar'),
            'calendarUrl' => $calendarUrl,
        ])
    @endif

    @if($moduleVisible('hashtag'))
        @include('invitations.partials.hashtag', ['hashtag' => $modulos['hashtag'] ?? []])
    @endif

    @if($moduleVisible('encuestas'))
        @include('invitations.partials.polls', [
            'encuestas' => $modulos['encuestas'] ?? [],
            'pollResults' => $pollResults,
            'slug' => $invitation->slug,
            'guestToken' => $guestToken,
        ])
    @endif

    @if($moduleVisible('playlist'))
        @include('invitations.partials.playlist', [
            'playlist' => $modulos['playlist'] ?? [],
            'slug' => $invitation->slug,
            'guestToken' => $guestToken,
            'songs' => $playlistSongs ?? [],
        ])
    @endif

    @if($moduleVisible('regalos'))
        @include('invitations.partials.regalos', ['regalos' => $modulos['regalos'] ?? []])
    @endif

    @if($moduleVisible('rsvp') && $guest)
        @include('invitations.partials.rsvp', [
            'rsvp' => $modulos['rsvp'] ?? [],
            'guest' => $guest,
            'slug' => $invitation->slug,
        ])
    @endif

    @if($moduleVisible('fotomural') && !$isPostEvent)
        @include('invitations.partials.fotomural', [
            'slug' => $invitation->slug,
            'guestToken' => $guestToken,
            'photos' => $fotomuralPhotos ?? [],
            'readOnly' => false,
        ])
    @endif

    @if($isPostEvent && $moduleVisible('post_evento'))
        @if($moduleVisible('fotomural'))
            @include('invitations.partials.fotomural', [
                'slug' => $invitation->slug,
                'guestToken' => $guestToken,
                'photos' => $fotomuralPhotos ?? [],
                'readOnly' => true,
            ])
        @endif
        @include('invitations.partials.post-event', ['postEvento' => $modulos['post_evento'] ?? []])
    @endif

    <footer class="py-12 text-center relative overflow-hidden">
        <!-- Gradient separator -->
        <div class="mb-8 h-px bg-gradient-to-r from-transparent via-primary to-transparent opacity-20"></div>
        
        <!-- Footer content wrapper with subtle background -->
        <div class="relative px-6">
            <div class="absolute inset-0 bg-gradient-to-b from-transparent via-accent to-transparent opacity-[0.02] pointer-events-none"></div>
            
            <div class="relative z-10 flex flex-col items-center gap-3">
                <!-- Decorative dots -->
                <div class="flex items-center gap-2">
                    <div class="w-1.5 h-1.5 rounded-full bg-primary opacity-30"></div>
                    <div class="w-1.5 h-1.5 rounded-full bg-primary opacity-50"></div>
                    <div class="w-1.5 h-1.5 rounded-full bg-primary opacity-30"></div>
                </div>
                
                <!-- Brand text -->
                <p class="text-[11px] tracking-[0.25em] uppercase font-light text-text-color opacity-40">
                    Creado con amor por
                </p>
                <p class="text-sm font-semibold tracking-[0.1em] text-primary">
                    Bida-Events
                </p>
                
                <!-- Decorative dots -->
                <div class="flex items-center gap-2">
                    <div class="w-1.5 h-1.5 rounded-full bg-primary opacity-30"></div>
                    <div class="w-1.5 h-1.5 rounded-full bg-primary opacity-50"></div>
                    <div class="w-1.5 h-1.5 rounded-full bg-primary opacity-30"></div>
                </div>
            </div>
        </div>
        
        <!-- Bottom gradient accent -->
        <div class="mt-8 h-px bg-gradient-to-r from-transparent via-primary to-transparent opacity-15"></div>
    </footer>

    <script>
    // ─── Utilidades móviles ───────────────────────────────────────────────────

    /**
     * Abre Google Calendar con preferencia por la app nativa en móvil.
     * Android  → intent:// hacia la app de Google Calendar
     * iOS      → enlace universal de Google Calendar
     * Desktop  → enlace web estándar
     */
    function openCalendar(webUrl) {
        const ua = navigator.userAgent || '';
        const isAndroid = /android/i.test(ua);
        const isIOS = /iphone|ipad|ipod/i.test(ua);

        if (isAndroid) {
            // Extraer parámetros del URL web para construir el intent
            const intentUrl = webUrl
                .replace('https://calendar.google.com/calendar/render', 'intent://calendar.google.com/calendar/render')
                + '#Intent;scheme=https;package=com.google.android.calendar;S.browser_fallback_url=' + encodeURIComponent(webUrl) + ';end';
            window.location.href = intentUrl;
        } else if (isIOS) {
            // Google Calendar app en iOS usa enlace universal
            window.location.href = webUrl.replace('https://calendar.google.com', 'https://calendar.google.com');
            // Fallback automático si la app no está instalada (el SO redirige a Safari)
        } else {
            window.open(webUrl, '_blank', 'noopener');
        }
    }


    // ─── App principal ────────────────────────────────────────────────────────
    function invitationApp() {
        return {
            init() {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(e => {
                        if (e.isIntersecting) {
                            e.target.classList.add('is-visible');
                            observer.unobserve(e.target);
                        }
                    });
                }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

                document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
            }
        };
    }
    </script>
</body>
</html>
