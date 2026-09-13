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
    $hasPlayer = $moduleVisible('musica') && !empty($musica['audio_url'] ?? null);
    $eventDate = $invitation->event_date->copy()->timezone(config('app.timezone'));
    $eventLabel = \Illuminate\Support\Str::ucfirst($eventDate->locale('es')->translatedFormat('l j \d\e F · H:i \h'));
    $guestName = $bienvenida['nombre_quinceanera'] ?? $invitation->title;

    // Pesos publicados en Google Fonts: pedir uno inexistente invalida toda la hoja de estilos
    $fontWeights = [
        'Playfair Display' => '400;600;700', 'Cormorant Garamond' => '400;600;700', 'Cinzel' => '400;600;700',
        'Libre Baskerville' => '400;700', 'Bodoni Moda' => '400;600;700', 'Lora' => '400;500;600;700',
        'Merriweather' => '300;400;700', 'Montserrat' => '300;400;500;600;700', 'Inter' => '300;400;500;600;700',
        'Lato' => '300;400;700', 'Nunito Sans' => '300;400;600;700', 'Source Sans 3' => '300;400;600;700',
        'Poppins' => '300;400;500;600;700', 'Raleway' => '300;400;500;600;700', 'Open Sans' => '300;400;600;700',
        'Dancing Script' => '400;700', 'Tangerine' => '400;700',
    ];
    $fontQuery = collect([
        $tipografias['titulos'] ?? 'Playfair Display',
        $tipografias['cuerpo'] ?? 'Montserrat',
        $tipografias['script'] ?? 'Great Vibes',
    ])
        ->filter()
        ->unique()
        ->map(fn ($family) => 'family=' . urlencode($family) . (isset($fontWeights[$family]) ? ':wght@' . $fontWeights[$family] : ''))
        ->implode('&');

    $navItems = [['id' => 'inicio', 'label' => 'Inicio']];
    if ($moduleVisible('rsvp') && $guest) {
        $navItems[] = ['id' => 'guest-banner', 'label' => 'Tu invitación'];
    }
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
        'rsvp' => 'Confirmar asistencia',
        'fotomural' => 'Fotomural',
        'post_evento' => 'Fotos oficiales',
    ] as $moduleKey => $label) {
        if (!$moduleVisible($moduleKey) || ($moduleKey === 'rsvp' && !$guest)) {
            continue;
        }

        $navItems[] = ['id' => str_replace('_', '-', $moduleKey), 'label' => $label];
    }
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="{{ $colores['background'] ?? '#FFFAF5' }}">
    <title>{{ $guestName }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?{{ $fontQuery }}&display=swap" rel="stylesheet">
    <style>
        html { scroll-behavior: smooth; }
        :root {
            --primary-color: {{ $colores['primary'] ?? '#C9A96E' }};
            --secondary-color: {{ $colores['secondary'] ?? '#2C1810' }};
            --accent-color: {{ $colores['accent'] ?? '#F5E6D3' }};
            --text-color: {{ $colores['text'] ?? '#1A1A1A' }};
            --bg-color: {{ $colores['background'] ?? '#FFFAF5' }};
            --surface-color: color-mix(in srgb, var(--accent-color) 32%, var(--bg-color));
            --font-titles: '{{ $tipografias['titulos'] ?? 'Playfair Display' }}', serif;
            --font-body: '{{ $tipografias['cuerpo'] ?? 'Montserrat' }}', sans-serif;
            --font-script: '{{ $tipografias['script'] ?? 'Great Vibes' }}', cursive;
        }
    </style>
</head>
<body class="inv-page overflow-x-hidden {{ $hasPlayer ? 'has-player' : '' }}" x-data="invitationApp()" x-init="init()">

    {{-- ═══ MENÚ DE SECCIONES ═══ --}}
    <div x-data="invitationNav(@js(array_column($navItems, 'id')))"
        @keydown.escape.window="open = false"
        x-effect="document.documentElement.classList.toggle('inv-lock', open)">
        <button type="button"
            class="inv-nav__toggle"
            :class="{ 'is-open': open }"
            @click="open = !open"
            :aria-expanded="open.toString()"
            aria-controls="inv-nav-panel">
            <span x-text="open ? 'Cerrar' : 'Menú'">Menú</span>
            <span class="inv-nav__bars" aria-hidden="true"><span></span><span></span></span>
        </button>

        <div class="inv-nav__backdrop" x-show="open" x-cloak x-transition.opacity @click="open = false"></div>

        <nav id="inv-nav-panel"
            class="inv-nav__panel"
            x-show="open" x-cloak
            x-transition:enter="inv-nav-anim"
            x-transition:enter-start="inv-nav-hidden"
            x-transition:enter-end="inv-nav-shown"
            x-transition:leave="inv-nav-anim"
            x-transition:leave-start="inv-nav-shown"
            x-transition:leave-end="inv-nav-hidden"
            aria-label="Secciones de la invitación">
            <p class="inv-label">Invitación de</p>
            <p class="inv-nav__heading">{{ $guestName }}</p>

            <ol class="inv-nav__list">
                @foreach($navItems as $index => $item)
                    <li>
                        <a href="#{{ $item['id'] }}"
                            class="inv-nav__link"
                            :class="{ 'is-active': active === '{{ $item['id'] }}' }"
                            @click="open = false">
                            <span class="inv-nav__num">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                            {{ $item['label'] }}
                        </a>
                    </li>
                @endforeach
            </ol>
        </nav>
    </div>

    @include('invitations.partials.music-player', ['musica' => $musica, 'flags' => array_merge($flags, ['musica' => $moduleVisible('musica')])])

    @include('invitations.partials.hero', [
        'invitation' => $invitation,
        'bienvenida' => $bienvenida,
        'heroImage' => $heroImage,
        'hasHeroImage' => $hasHeroImage,
    ])

    <main id="contenido">
        @if($moduleVisible('rsvp') && $guest)
            @include('invitations.partials.guest-banner', ['guest' => $guest])
        @endif

        @if($isPostEvent && $moduleVisible('post_evento') && !empty($bienvenida['mensaje_post_evento']))
            <section class="inv-section reveal">
                <div class="inv-wrap">
                    <p class="inv-thanks__text">{{ $bienvenida['mensaje_post_evento'] }}</p>
                </div>
            </section>
        @endif

        @if($moduleVisible('cuenta_regresiva') && !$isPostEvent)
            @include('invitations.partials.countdown', [
                'eventDate' => $eventDate->toIso8601String(),
                'eventLabel' => $eventLabel,
                'calendarUrl' => $calendarUrl,
                'agendar' => $moduleVisible('agendar'),
            ])
        @endif

        @if(!$moduleVisible('cuenta_regresiva') && $moduleVisible('agendar') && !$isPostEvent)
            <section class="inv-section reveal" id="agendar">
                <div class="inv-wrap">
                    @include('invitations.partials.section-header', [
                        'lottie' => 'calendar',
                        'eyebrow' => 'Guarda la fecha',
                        'title' => 'Agéndalo',
                        'intro' => $eventLabel,
                    ])
                    <div class="inv-actions">
                        <button type="button" class="inv-btn inv-btn--block" data-url="{{ $calendarUrl }}" onclick="openCalendar(this.dataset.url)">
                            Agregar a Google Calendar
                        </button>
                    </div>
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
                'agendar' => $moduleVisible('agendar') && !$isPostEvent,
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

        @if($moduleVisible('fotomural'))
            @include('invitations.partials.fotomural', [
                'slug' => $invitation->slug,
                'guestToken' => $guestToken,
                'photos' => $fotomuralPhotos ?? [],
                'readOnly' => $isPostEvent,
            ])
        @endif

        @if($isPostEvent && $moduleVisible('post_evento'))
            @include('invitations.partials.post-event', ['postEvento' => $modulos['post_evento'] ?? []])
        @endif
    </main>

    <footer class="inv-footer">
        <p class="inv-footer__text">Hecho con cariño por <span class="inv-footer__brand">Bida Events</span></p>
    </footer>

    <script>
    /**
     * Abre Google Calendar priorizando la app nativa en Android;
     * en iOS y escritorio el enlace universal resuelve solo.
     */
    function openCalendar(webUrl) {
        if (/android/i.test(navigator.userAgent || '')) {
            window.location.href = webUrl.replace('https://calendar.google.com/calendar/render', 'intent://calendar.google.com/calendar/render')
                + '#Intent;scheme=https;package=com.google.android.calendar;S.browser_fallback_url=' + encodeURIComponent(webUrl) + ';end';
            return;
        }

        window.open(webUrl, '_blank', 'noopener');
    }

    async function copyToClipboard(text) {
        try {
            await navigator.clipboard.writeText(text);

            return true;
        } catch (error) {
            // Respaldo para navegadores sin Clipboard API o contextos no seguros
            const field = document.createElement('textarea');
            field.value = text;
            field.setAttribute('readonly', '');
            field.style.position = 'fixed';
            field.style.opacity = '0';
            document.body.appendChild(field);
            field.select();
            const copied = document.execCommand('copy');
            field.remove();

            return copied;
        }
    }

    // Botón "Copiar" con confirmación temporal; `key` distingue varios botones en un mismo bloque
    function copyButton() {
        return {
            copied: null,
            timer: null,
            async copy(text, key = 'default') {
                if (!(await copyToClipboard(text))) {
                    return;
                }

                this.copied = key;
                clearTimeout(this.timer);
                this.timer = setTimeout(() => { this.copied = null; }, 1800);
            },
        };
    }

    function invitationApp() {
        return {
            init() {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('is-visible');
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.08, rootMargin: '0px 0px -40px 0px' });

                document.querySelectorAll('.reveal').forEach((element) => observer.observe(element));
            }
        };
    }

    // Menú: resalta la sección que ocupa el centro de la pantalla
    function invitationNav(sectionIds) {
        return {
            open: false,
            active: sectionIds[0] ?? 'inicio',
            init() {
                if (!('IntersectionObserver' in window)) {
                    return;
                }

                const observer = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            this.active = entry.target.id;
                        }
                    });
                }, { rootMargin: '-45% 0px -50% 0px' });

                sectionIds.forEach((id) => {
                    const section = document.getElementById(id);

                    if (section) {
                        observer.observe(section);
                    }
                });
            },
        };
    }
    </script>
</body>
</html>
