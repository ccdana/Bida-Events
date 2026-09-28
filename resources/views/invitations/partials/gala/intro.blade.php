{{--
    Apertura de «Noche de gala»: el gran salón a oscuras, con la araña de cristal apagada colgando del
    techo y, debajo, a quién espera la noche. Al tocar la araña se encienden sus velas una por una,
    los caireles lanzan destellos y la luz baja e ilumina el salón; la araña sube y aparece la portada.
    Lógica en shell/cover-component; estilos en themes/gala.css.
--}}
@php
    $introDate = \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('j \d\e F \d\e Y'));
    $introEyebrow = ($page->welcome['subtitulo'] ?? null) ?: ($invCopy['hero_eyebrow'] ?? 'Mis XV años');
@endphp

<div class="inv-themed-intro ga-intro"
    x-data="invitationCover({ part: 1500, reveal: 2050, close: 3000 })"
    x-show="!closed"
    :class="{ 'is-lit': stage >= 1, 'is-open': stage >= 2 }"
    @click="open()"
    role="dialog"
    aria-modal="true"
    aria-label="Invitación a los XV años de {{ $page->displayName }}">
    <span class="ga-intro__light" aria-hidden="true"></span>

    <button type="button" class="ga-intro__chandelier" data-cover-trigger aria-label="Encender la araña y entrar al salón">
        @include('invitations.partials.gala.chandelier')
    </button>

    <div class="ga-intro__text">
        <p class="ga-intro__guest">
            @if($guest)
                {{ $invCopy['intro_eyebrow'] ?? 'Esta noche te espera' }}, {{ $guest->name }}
            @else
                {{ $invCopy['intro_eyebrow'] ?? 'Esta noche te espera' }}
            @endif
        </p>
        <p class="ga-intro__name">{{ $page->displayName }}</p>
        <p class="ga-intro__date">{{ $introEyebrow }} · {{ $introDate }}</p>
    </div>

    <p class="ga-intro__hint">{{ $invCopy['intro_hint'] ?? 'Toca la araña para encender el salón' }}</p>
</div>
