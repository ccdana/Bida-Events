@php
    $config = $modulos['config'] ?? [];
    $colores = $config['colores'] ?? [];
    $tipografias = $config['tipografias'] ?? [];
    $flags = $config['modulos'] ?? [];
    $bienvenida = $modulos['bienvenida'] ?? [];
    $musica = $modulos['musica'] ?? [];
    $isPostEvent = $invitation->isPostEvent();
    $guestToken = $guest?->qr_code_token ?? '';
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

    {{-- Partículas sutiles (puntos CSS, sin emojis) --}}
    <div class="fixed inset-0 pointer-events-none z-0 overflow-hidden" aria-hidden="true">
        @for($i = 0; $i < 10; $i++)
            <span class="particle-dot absolute w-1 h-1 rounded-full bg-primary"
                style="left:{{ rand(5, 95) }}%;top:{{ rand(5, 95) }}%;animation-delay:{{ $i * 0.6 }}s"></span>
        @endfor
    </div>

    {{-- Reproductor de música (configurable por admin) --}}
    @include('invitations.partials.music-player', ['musica' => $musica, 'flags' => $flags])

    {{-- HERO --}}
    <header class="relative min-h-[100dvh] flex flex-col items-center justify-center text-center px-8 py-24 z-10">
        @if($isPostEvent && ($flags['post_evento'] ?? false))
            <p class="font-script text-4xl sm:text-5xl text-primary mb-6 leading-tight max-w-sm">{{ $bienvenida['mensaje_post_evento'] ?? 'Gracias por acompañarme en mi noche magica' }}</p>
        @else
            <p class="text-[10px] uppercase tracking-[0.35em] text-primary/70 mb-5">{{ $bienvenida['subtitulo'] ?? 'Mis XV Anos' }}</p>
            <h1 class="font-script text-6xl sm:text-[5.5rem] text-primary leading-none mb-8">{{ $bienvenida['nombre_quinceanera'] ?? 'Quinceanera' }}</h1>
            <p class="font-title text-lg sm:text-xl max-w-sm leading-relaxed opacity-75">{{ $bienvenida['mensaje'] ?? '' }}</p>
            <p class="mt-10 text-xs tracking-[0.25em] uppercase border-t border-b border-primary/25 py-4 px-8">{{ $bienvenida['fecha_texto'] ?? $invitation->event_date->format('d \d\e F, Y') }}</p>
        @endif

        @if($guest)
            <div class="mt-12 px-8 py-5 rounded-2xl border border-primary/30 bg-white/50 backdrop-blur-md max-w-sm w-full">
                <p class="text-[10px] uppercase tracking-widest opacity-50">Invitacion personal</p>
                <p class="font-title text-xl mt-2">{{ $guest->name }}</p>
                @if($guest->status === 'pending')
                    <p class="text-sm text-primary mt-3">Tienes <strong>{{ $guest->passes_allocated }}</strong> {{ $guest->passes_allocated === 1 ? 'pase disponible' : 'pases disponibles' }}</p>
                @endif
            </div>
        @endif

        <div class="absolute bottom-10 text-primary/40">
            @include('invitations.partials.icon', ['name' => 'chevron-down', 'class' => 'w-6 h-6'])
        </div>
    </header>

    @if(($flags['cuenta_regresiva'] ?? false) && !$isPostEvent)
        @include('invitations.partials.countdown', ['eventDate' => $invitation->event_date->toIso8601String()])
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
        @include('invitations.partials.fotomural', ['slug' => $invitation->slug, 'guestToken' => $guestToken])
    @endif

    @if($isPostEvent && ($flags['post_evento'] ?? false))
        @include('invitations.partials.post-event', ['postEvento' => $modulos['post_evento'] ?? []])
    @endif

    <footer class="py-16 text-center z-10 relative">
        <p class="text-[10px] tracking-[0.3em] uppercase opacity-35">Disenado por <span class="text-primary opacity-100">Bida-Events</span></p>
    </footer>

    <script>
    function invitationApp() {
        return { init() {} };
    }
    </script>
</body>
</html>
