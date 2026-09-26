{{--
    Apertura de «Dos caminos»: un mapa doblado al medio. En la tapa, las iniciales y la fecha; al
    tocarlo se despliega la otra mitad y aparecen los dos caminos que llegan al mismo aro. Después el
    mapa se acerca al punto de encuentro y deja ver la portada. Estilos en themes/caminos.css.
--}}
@php
    $introDate = \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('j \d\e F \d\e Y'));
    // Los dos caminos del mapa, en el mismo dibujo para las dos mitades
    $routes = '<svg viewBox="0 0 200 150" preserveAspectRatio="none" focusable="false">'
        .'<path class="dc-route dc-route--a" d="M14 22 C40 30 46 70 72 62 S94 70 100 75"/>'
        .'<path class="dc-route dc-route--b" d="M186 128 C160 122 158 88 132 96 S106 82 100 75"/>'
        .'<circle class="dc-map-ring" cx="100" cy="75" r="7"/>'
        .'</svg>';
@endphp

<div class="inv-themed-intro dc-intro"
    x-data="invitationCover({ part: 800, reveal: 1600, close: 2500 })"
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
                <span class="dc-map__initials">{{ $page->initials() }}</span>
                <span class="dc-map__date">{{ $introDate }}</span>
            </span>
        </span>
    </button>

    <p class="dc-intro__hint">{{ $invCopy['intro_hint'] ?? 'Toca el mapa para desplegarlo' }}</p>
</div>
