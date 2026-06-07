@php
    $config = $modulos['config'] ?? [];
    $colores = $config['colores'] ?? [];
    $tipografias = $config['tipografias'] ?? [];
    $flags = $config['modulos'] ?? [];
    $bienvenida = $modulos['bienvenida'] ?? [];
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
    </style>
</head>
<body class="overflow-x-hidden" x-data="invitationApp()" x-init="init()">

    {{-- Partículas flotantes --}}
    <div class="fixed inset-0 pointer-events-none z-0 overflow-hidden" aria-hidden="true">
        <template x-for="p in particles" :key="p.id">
            <span class="absolute text-primary opacity-30 animate-float" :style="`left:${p.x}%;top:${p.y}%;animation-delay:${p.delay}s;font-size:${p.size}px`" x-text="p.char"></span>
        </template>
    </div>

    {{-- Reproductor música flotante --}}
    @if(($flags['musica'] ?? false) && !empty($modulos['musica']['audio_url']))
    <div class="fixed bottom-24 right-4 z-50" x-data="{ playing: false, volume: 0.5 }">
        <audio x-ref="audio" src="{{ $modulos['musica']['audio_url'] }}" loop preload="metadata"></audio>
        <button @click="playing = !playing; playing ? $refs.audio.play() : $refs.audio.pause()"
            class="w-12 h-12 rounded-full bg-primary/90 text-white shadow-lg flex items-center justify-center backdrop-blur-sm">
            <span x-text="playing ? '⏸' : '▶'"></span>
        </button>
    </div>
    @endif

    {{-- HERO --}}
    <header class="relative min-h-screen flex flex-col items-center justify-center text-center px-6 py-20 z-10">
        @if($isPostEvent && ($flags['post_evento'] ?? false))
            <p class="font-script text-4xl sm:text-5xl text-primary mb-4">{{ $bienvenida['mensaje_post_evento'] ?? '¡Gracias por acompañarme!' }}</p>
        @else
            <p class="text-xs uppercase tracking-[0.3em] text-primary/80 mb-4">{{ $bienvenida['subtitulo'] ?? 'Mis XV Años' }}</p>
            <h1 class="font-script text-6xl sm:text-8xl text-primary leading-none mb-6">{{ $bienvenida['nombre_quinceanera'] ?? 'Quinceañera' }}</h1>
            <p class="font-title text-lg sm:text-xl max-w-md leading-relaxed opacity-80">{{ $bienvenida['mensaje'] ?? '' }}</p>
            <p class="mt-8 text-sm tracking-widest uppercase border-t border-b border-primary/30 py-3 px-6">{{ $bienvenida['fecha_texto'] ?? $invitation->event_date->format('d \d\e F, Y') }}</p>
        @endif

        @if($guest)
            <div class="mt-10 px-6 py-4 rounded-2xl border border-primary/40 bg-white/60 backdrop-blur-sm max-w-sm">
                <p class="text-sm opacity-70">Invitación personal para</p>
                <p class="font-title text-xl mt-1">{{ $guest->name }}</p>
                @if($guest->status === 'pending')
                    <p class="text-sm text-primary mt-2">Tienes <strong>{{ $guest->passes_allocated }}</strong> {{ $guest->passes_allocated === 1 ? 'pase' : 'pases' }} disponibles</p>
                @endif
            </div>
        @endif

        <div class="absolute bottom-8 animate-bounce opacity-50">↓</div>
    </header>

    {{-- CUENTA REGRESIVA --}}
    @if(($flags['cuenta_regresiva'] ?? false) && !$isPostEvent)
        @include('invitations.partials.countdown', ['eventDate' => $invitation->event_date->toIso8601String()])
    @endif

    {{-- VIDEO SAVE THE DATE --}}
    @if(($flags['video'] ?? false) && !empty($modulos['video']['video_url']))
        @include('invitations.partials.video', ['video' => $modulos['video']])
    @endif

    {{-- GALERÍA STACK --}}
    @if(($flags['galeria'] ?? false) && !empty($modulos['galeria']['fotos']))
        @include('invitations.partials.gallery-stack', ['galeria' => $modulos['galeria']])
    @endif

    {{-- ITINERARIO --}}
    @if($flags['itinerario'] ?? false)
        @include('invitations.partials.itinerary', ['itinerario' => $modulos['itinerario'] ?? []])
    @endif

    {{-- DRESS CODE --}}
    @if($flags['dress_code'] ?? false)
        @include('invitations.partials.dress-code', ['dressCode' => $modulos['dress_code'] ?? []])
    @endif

    {{-- DESTACADOS --}}
    @if($flags['destacados'] ?? false)
        @include('invitations.partials.destacados', ['destacados' => $modulos['destacados'] ?? []])
    @endif

    {{-- UBICACIÓN + TRANSPORTE --}}
    @if($flags['ubicacion'] ?? false)
        @include('invitations.partials.location', [
            'ubicacion' => $modulos['ubicacion'] ?? [],
            'transporte' => $flags['transporte'] ?? false,
            'agendar' => $flags['agendar'] ?? false,
            'calendarUrl' => $calendarUrl,
        ])
    @endif

    {{-- HASHTAG --}}
    @if($flags['hashtag'] ?? false)
        @include('invitations.partials.hashtag', ['hashtag' => $modulos['hashtag'] ?? []])
    @endif

    {{-- ENCUESTAS --}}
    @if($flags['encuestas'] ?? false)
        @include('invitations.partials.polls', [
            'encuestas' => $modulos['encuestas'] ?? [],
            'pollResults' => $pollResults,
            'slug' => $invitation->slug,
            'guestToken' => $guestToken,
        ])
    @endif

    {{-- PLAYLIST --}}
    @if($flags['playlist'] ?? false)
        @include('invitations.partials.playlist', [
            'playlist' => $modulos['playlist'] ?? [],
            'slug' => $invitation->slug,
            'guestToken' => $guestToken,
        ])
    @endif

    {{-- REGALOS --}}
    @if($flags['regalos'] ?? false)
        @include('invitations.partials.regalos', ['regalos' => $modulos['regalos'] ?? []])
    @endif

    {{-- RSVP --}}
    @if(($flags['rsvp'] ?? false) && $guest)
        @include('invitations.partials.rsvp', [
            'rsvp' => $modulos['rsvp'] ?? [],
            'guest' => $guest,
            'slug' => $invitation->slug,
        ])
    @endif

    {{-- FOTOMURAL --}}
    @if(($flags['fotomural'] ?? false) && !$isPostEvent)
        @include('invitations.partials.fotomural', ['slug' => $invitation->slug, 'guestToken' => $guestToken])
    @endif

    {{-- POST EVENTO GALERÍA --}}
    @if($isPostEvent && ($flags['post_evento'] ?? false))
        @include('invitations.partials.post-event', ['postEvento' => $modulos['post_evento'] ?? []])
    @endif

    {{-- FOOTER --}}
    <footer class="py-12 text-center text-xs tracking-widest uppercase opacity-40 z-10 relative">
        Diseñado con ♡ por <span class="text-primary">Bida-Events</span>
    </footer>

    <script>
    function invitationApp() {
        return {
            particles: [],
            init() {
                const chars = ['✦', '✧', '·', '♡'];
                this.particles = Array.from({ length: 12 }, (_, i) => ({
                    id: i,
                    x: Math.random() * 100,
                    y: Math.random() * 100,
                    delay: Math.random() * 4,
                    size: 8 + Math.random() * 12,
                    char: chars[i % chars.length]
                }));
            }
        };
    }
    </script>
</body>
</html>
