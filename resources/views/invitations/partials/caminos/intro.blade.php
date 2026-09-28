{{--
    Apertura de «Dos caminos»: un mapa doblado al medio. En la tapa, las iniciales y la fecha; al
    tocarlo se despliega la otra mitad y aparecen los dos caminos. Cada uno de los novios es un punto
    con su inicial que recorre su camino —dejando la huella— hasta el aro donde se juntan: el aro late,
    aparece un corazón y el mapa se acerca al punto de encuentro hasta dejar ver la portada.
    Estilos en themes/caminos.css.
--}}
@php
    $introDate = \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('j \d\e F \d\e Y'));
    // La inicial de cada uno para el punto que recorre su camino
    $walkers = collect($page->names())->take(2)->map(fn (string $name) => mb_strtoupper(mb_substr(trim($name), 0, 1)))->pad(2, '')->all();
    $routeA = 'M14 22 C40 30 46 70 72 62 S94 70 100 75';
    $routeB = 'M186 128 C160 122 158 88 132 96 S106 82 100 75';
    // Los dos caminos del mapa, en el mismo dibujo para las dos mitades
    $routes = '<svg viewBox="0 0 200 150" preserveAspectRatio="none" focusable="false">'
        .'<path class="dc-route dc-route--a" d="M14 22 C40 30 46 70 72 62 S94 70 100 75"/>'
        .'<path class="dc-route dc-route--b" d="M186 128 C160 122 158 88 132 96 S106 82 100 75"/>'
        .'<circle class="dc-map-ring" cx="100" cy="75" r="7"/>'
        .'</svg>';
@endphp

<div class="inv-themed-intro dc-intro"
    x-data="invitationCover({ part: 2500, reveal: 2900, close: 3600 })"
    x-show="!closed"
    :class="{ 'is-unfolding': stage >= 1, 'is-open': stage >= 2 }"
    role="dialog"
    aria-modal="true"
    aria-label="Invitación a la boda de {{ $page->displayName }}">
    <p class="dc-intro__eyebrow">
        @if($guest)
            {{ $guest->name }}, {{ mb_strtolower($invCopy['intro_eyebrow'] ?? 'Te mandamos un mapa') }}
        @else
            {{ $invCopy['intro_eyebrow'] ?? 'Te mandamos un mapa' }}
        @endif
    </p>

    <button type="button" class="dc-map" data-cover-trigger @click="open()" aria-label="Desplegar el mapa y abrir la invitación">
        <span class="dc-map__half dc-map__half--left" aria-hidden="true">{!! $routes !!}</span>
        <span class="dc-map__flap" aria-hidden="true">
            <span class="dc-map__half dc-map__half--right">{!! $routes !!}</span>
            <span class="dc-map__cover">
                {{-- La rosa de los vientos de la tapa --}}
                <svg class="dc-map__compass" viewBox="0 0 60 60" aria-hidden="true" focusable="false">
                    <circle cx="30" cy="30" r="21"/>
                    <circle cx="30" cy="30" r="17" class="dc-map__compass-thin"/>
                    <path class="dc-map__compass-star" d="M30 6 L34 26 L54 30 L34 34 L30 54 L26 34 L6 30 L26 26 Z"/>
                    <path class="dc-map__compass-north" d="M30 6 L34 26 L30 30 L26 26 Z"/>
                </svg>
                <span class="dc-map__initials">{{ $page->initials() }}</span>
                <span class="dc-map__date">{{ $introDate }}</span>
            </span>
        </span>

        {{-- Los dos que caminan: cuando el mapa termina de abrirse, cada punto recorre su camino hasta el aro --}}
        <svg class="dc-map__walk" viewBox="0 0 200 150" preserveAspectRatio="none" aria-hidden="true" focusable="false"
            x-data="{ walked: false }"
            x-effect="if (stage >= 1 && !walked) { walked = true; setTimeout(() => $el.querySelectorAll('animateMotion').forEach((motion) => motion.beginElement()), 850) }">
            <path class="dc-walk__trail dc-walk__trail--a" pathLength="1" d="{{ $routeA }}"/>
            <path class="dc-walk__trail dc-walk__trail--b" pathLength="1" d="{{ $routeB }}"/>
            <circle class="dc-walk__meet" cx="100" cy="75" r="8"/>
            <path class="dc-walk__heart" d="M100 81 C92 75 89 71 89 67.5 C89 64.5 91.5 62.5 94.3 62.5 C96.8 62.5 98.8 64 100 66 C101.2 64 103.2 62.5 105.7 62.5 C108.5 62.5 111 64.5 111 67.5 C111 71 108 75 100 81 Z"/>
            @foreach(['a' => $routeA, 'b' => $routeB] as $side => $route)
                <g class="dc-walk__pin dc-walk__pin--{{ $side }}">
                    <circle r="7"/>
                    <text y="0.5" text-anchor="middle" dominant-baseline="middle">{{ $walkers[$side === 'a' ? 0 : 1] }}</text>
                    <animateMotion dur="1.25s" begin="indefinite" fill="freeze" calcMode="spline" keyTimes="0;1" keySplines="0.45 0 0.25 1" path="{{ $route }}"/>
                </g>
            @endforeach
        </svg>
    </button>

    <p class="dc-intro__hint">{{ $invCopy['intro_hint'] ?? 'Toca el mapa para desplegarlo' }}</p>
</div>
