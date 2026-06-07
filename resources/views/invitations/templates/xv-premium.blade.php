@php
    $config = $modulos['config'] ?? [];
    $colores = $config['colores'] ?? [];
    $tipografias = $config['tipografias'] ?? [];
    $flags = $config['modulos'] ?? [];
    $bienvenida = $modulos['bienvenida'] ?? [];
    $musica = $modulos['musica'] ?? [];
    $isPostEvent = $invitation->isPostEvent();
    $guestToken = $guest?->qr_code_token ?? '';
    $heroImage = $bienvenida['imagen_hero'] ?? null;
    $hasHeroImage = !empty($heroImage);
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
    <style>
        :root {
            --primary-color: {{ $colores['primary'] ?? '#C9A96E' }};
            --secondary-color: {{ $colores['secondary'] ?? '#2C1810' }};
            --accent-color: {{ $colores['accent'] ?? '#F5E6D3' }};
            --text-color: {{ $colores['text'] ?? '#1A1A1A' }};
            --bg-color: {{ $colores['background'] ?? '#FFFAF5' }};
            --font-titles: '{{ $tipografias['titulos'] ?? 'Playfair Display' }}', serif;
            --font-body: '{{ $tipografias['cuerpo'] ?? 'Montserrat' }}', sans-serif;
            --font-script: '{{ $tipografias['script'] ?? 'Great Vibes' }}', cursive;
        }
        body { font-family: var(--font-body); color: var(--text-color); background: var(--bg-color); }
        .font-title { font-family: var(--font-titles); }
        .font-script { font-family: var(--font-script); }
        .text-primary { color: var(--primary-color); }
        .bg-primary { background-color: var(--primary-color); }
        .border-primary { border-color: var(--primary-color); }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
    </style>
</head>
<body class="overflow-x-hidden pb-28" x-data="invitationApp()" x-init="init()">

    <div class="fixed inset-0 pointer-events-none z-0 overflow-hidden" aria-hidden="true">
        @for($i = 0; $i < 12; $i++)
            <span class="particle-dot absolute w-1 h-1 rounded-full bg-primary"
                style="left:{{ rand(5, 95) }}%;top:{{ rand(5, 95) }}%;animation-delay:{{ $i * 0.5 }}s"></span>
        @endfor
    </div>

    @include('invitations.partials.music-player', ['musica' => $musica, 'flags' => $flags])

    {{-- HERO --}}
    <header class="relative min-h-[100dvh] flex flex-col items-center justify-center text-center overflow-hidden">
        <div class="hero-bg {{ $hasHeroImage ? '' : 'hero-no-image' }}">
            @if($hasHeroImage)
                <img src="{{ $heroImage }}" alt="" class="hero-ken-burns" loading="eager">
                <div class="hero-overlay"></div>
            @endif
        </div>

        <div class="hero-content px-6 py-20 sm:px-8 w-full max-w-lg {{ $hasHeroImage ? 'hero-text-light' : '' }}">
            @if($isPostEvent && ($flags['post_evento'] ?? false))
                <p class="font-script text-4xl sm:text-5xl text-primary mb-6 leading-tight max-w-sm mx-auto animate-fade-up {{ $hasHeroImage ? '!text-white' : '' }}">
                    {{ $bienvenida['mensaje_post_evento'] ?? 'Gracias por acompañarme en mi noche mágica' }}
                </p>
            @else
                <p class="text-[10px] uppercase tracking-[0.35em] mb-5 animate-fade-up {{ $hasHeroImage ? 'text-white/75' : 'text-primary/70' }}">
                    {{ $bienvenida['subtitulo'] ?? 'Mis XV Años' }}
                </p>
                <h1 class="font-script text-6xl sm:text-[5.5rem] leading-none mb-6 animate-fade-up animate-fade-up-delay-1 {{ $hasHeroImage ? 'text-white drop-shadow-lg' : 'text-primary' }}">
                    {{ $bienvenida['nombre_quinceanera'] ?? 'Quinceañera' }}
                </h1>
                @if(!empty($bienvenida['mensaje']))
                    <p class="font-title text-base sm:text-lg max-w-sm mx-auto leading-relaxed animate-fade-up animate-fade-up-delay-2 {{ $hasHeroImage ? 'text-white/85' : 'opacity-75' }}">
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

    @if(($flags['cuenta_regresiva'] ?? false) && !$isPostEvent)
        @include('invitations.partials.countdown', [
            'eventDate' => $invitation->event_date->toIso8601String(),
            'calendarUrl' => $calendarUrl,
            'agendar' => $flags['agendar'] ?? false,
        ])
    @endif

    @if(!($flags['cuenta_regresiva'] ?? false) && ($flags['agendar'] ?? false) && !$isPostEvent)
        <section class="invitation-section reveal py-6">
            <div class="section-inner text-center">
                <a href="{{ $calendarUrl }}" target="_blank" rel="noopener"
                    class="inline-flex items-center gap-2 px-6 py-3 rounded-full inv-card text-sm font-medium text-primary active:scale-[0.98] transition-transform">
                    @include('invitations.partials.icon', ['name' => 'calendar', 'class' => 'w-4 h-4', 'animated' => false])
                    Agendar en Google Calendar
                </a>
            </div>
        </section>
    @endif

    @if(($flags['video'] ?? false) && !empty($modulos['video']['video_url']))
        @include('invitations.partials.video', ['video' => $modulos['video']])
    @endif

    @if(($flags['galeria'] ?? false) && !empty($modulos['galeria']['fotos']))
        @include('invitations.partials.gallery-stack', ['galeria' => $modulos['galeria']])
    @endif

    @if($flags['itinerario'] ?? false)
        @include('invitations.partials.itinerary', ['itinerario' => $modulos['itinerario'] ?? []])
    @endif

    @if($flags['dress_code'] ?? false)
        @include('invitations.partials.dress-code', ['dressCode' => $modulos['dress_code'] ?? []])
    @endif

    @if($flags['destacados'] ?? false)
        @include('invitations.partials.destacados', ['destacados' => $modulos['destacados'] ?? []])
    @endif

    @if($flags['ubicacion'] ?? false)
        @include('invitations.partials.location', [
            'ubicacion' => $modulos['ubicacion'] ?? [],
            'transporte' => $flags['transporte'] ?? false,
            'agendar' => $flags['agendar'] ?? false,
            'calendarUrl' => $calendarUrl,
        ])
    @endif

    @if($flags['hashtag'] ?? false)
        @include('invitations.partials.hashtag', ['hashtag' => $modulos['hashtag'] ?? []])
    @endif

    @if($flags['encuestas'] ?? false)
        @include('invitations.partials.polls', [
            'encuestas' => $modulos['encuestas'] ?? [],
            'pollResults' => $pollResults,
            'slug' => $invitation->slug,
            'guestToken' => $guestToken,
        ])
    @endif

    @if($flags['playlist'] ?? false)
        @include('invitations.partials.playlist', [
            'playlist' => $modulos['playlist'] ?? [],
            'slug' => $invitation->slug,
            'guestToken' => $guestToken,
            'songs' => $playlistSongs ?? [],
        ])
    @endif

    @if($flags['regalos'] ?? false)
        @include('invitations.partials.regalos', ['regalos' => $modulos['regalos'] ?? []])
    @endif

    @if(($flags['rsvp'] ?? false) && $guest)
        @include('invitations.partials.rsvp', [
            'rsvp' => $modulos['rsvp'] ?? [],
            'guest' => $guest,
            'slug' => $invitation->slug,
        ])
    @endif

    @if(($flags['fotomural'] ?? false) && !$isPostEvent)
        @include('invitations.partials.fotomural', [
            'slug' => $invitation->slug,
            'guestToken' => $guestToken,
            'photos' => $fotomuralPhotos ?? [],
        ])
    @endif

    @if($isPostEvent && ($flags['post_evento'] ?? false))
        @include('invitations.partials.post-event', ['postEvento' => $modulos['post_evento'] ?? []])
    @endif

    <footer class="py-10 text-center relative">
        <div class="section-ornament mb-4"></div>
        <p class="text-[10px] tracking-[0.3em] uppercase opacity-35">Diseñado por <span class="text-primary opacity-100">Bida-Events</span></p>
    </footer>

    <script>
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
