{{--
    La invitación «como historias de Instagram» (resources/js/story/story.js con optIn y autoplay):
    cada parte de la página es una historia vertical que avanza sola con su barra arriba. Tocar a la
    derecha pasa, a la izquierda vuelve, mantener presionado pausa y la cruz vuelve a la página.
    Se abre con el círculo de la esquina, desde el menú o con ?historias en el enlace.
    Necesita $page. Estilos en resources/css/invitation/story.css (sección «Historias»).
--}}
@vite('resources/css/invitation/story.css')

@php
    $storyAvatar = $page->heroImage ? \App\Support\CloudinaryImage::url($page->heroImage, 120) : null;
    $storyDate = \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('D j M'));
@endphp

<div class="inv-ig" data-ig-stories x-data="invitationStory(@js(['enabled' => empty($isPreview), 'optIn' => true, 'autoplay' => true]))"
    :class="{ 'is-paused': paused || heldPause }"
    @scroll.window.throttle.150ms="atTop = window.scrollY < window.innerHeight * 0.6">

    {{-- Fondo oscuro detrás de la historia (en la computadora se ve alrededor, como en Instagram) --}}
    <div class="inv-ig__backdrop" x-show="on" x-cloak @click="leave()"></div>

    <div class="inv-ig__top" x-show="on" x-cloak>
        <div class="inv-story__progress inv-ig__progress" aria-hidden="true">
            <template x-for="position in count" :key="position">
                <span class="inv-story__segment" :class="{ 'is-done': position - 1 < index, 'is-current': position - 1 === index }"><i></i></span>
            </template>
        </div>

        <div class="inv-ig__bar">
            <span class="inv-ig__avatar" aria-hidden="true">
                @if($storyAvatar)
                    <img src="{{ $storyAvatar }}" alt="" loading="lazy" decoding="async">
                @else
                    <span>{{ $page->initials() }}</span>
                @endif
            </span>
            <p class="inv-ig__who">
                <span class="inv-ig__name">{{ $page->displayName }}</span>
                <span class="inv-ig__date">{{ $storyDate }}</span>
            </p>

            <button type="button" class="inv-ig__icon" @click="togglePause()" :aria-label="paused ? 'Seguir' : 'Pausar'">
                <svg x-show="!paused" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/></svg>
                <svg x-show="paused" x-cloak viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5.5v13a1 1 0 0 0 1.5.86l10.4-6.5a1 1 0 0 0 0-1.72L9.5 4.64A1 1 0 0 0 8 5.5z"/></svg>
            </button>
            <button type="button" class="inv-ig__icon" x-show="hasSound" x-cloak @click="toggleSound()" :aria-label="soundOn ? 'Silenciar la música' : 'Escuchar la música'">
                <svg x-show="soundOn" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 9v6h4l5 4V5L8 9H4z" fill="currentColor" stroke="none"/><path d="M16.5 8.5a5 5 0 0 1 0 7M19 6a8.5 8.5 0 0 1 0 12"/></svg>
                <svg x-show="!soundOn" x-cloak viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" aria-hidden="true"><path d="M4 9v6h4l5 4V5L8 9H4z" fill="currentColor" stroke="none"/><path d="M17 9.5l5 5M22 9.5l-5 5"/></svg>
            </button>
            <button type="button" class="inv-ig__icon" @click="leave()" aria-label="Cerrar las historias y ver la página">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </div>
    </div>

    {{-- En la computadora: flechas a los costados de la historia --}}
    <button type="button" class="inv-ig__nav inv-ig__nav--prev" x-show="on && index > 0" x-cloak @click="prev(true)" aria-label="Historia anterior">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 5l-7 7 7 7"/></svg>
    </button>
    <button type="button" class="inv-ig__nav inv-ig__nav--next" x-show="on" x-cloak @click="index === count - 1 ? go(0, { focus: true }) : next(true)"
        :aria-label="index === count - 1 ? 'Volver a empezar' : 'Historia siguiente'">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 5l7 7-7 7"/></svg>
    </button>

    <p class="inv-story__hint inv-ig__hint" x-show="on && index === 0 && !moved" x-cloak aria-hidden="true">
        <span class="inv-story__hint-ring"></span>
        {{ $invCopy['stories_hint'] ?? 'Toca para avanzar · mantén para pausar' }}
    </p>

    {{-- El círculo con aro de color: el botón para verla como historia --}}
    <button type="button" class="inv-ig__launcher" x-show="ready && !on && atTop" x-cloak x-transition.opacity.duration.300ms @click="start()">
        <span class="inv-ig__ring" aria-hidden="true">
            <span class="inv-ig__avatar">
                @if($storyAvatar)
                    <img src="{{ $storyAvatar }}" alt="" loading="lazy" decoding="async">
                @else
                    <span>{{ $page->initials() }}</span>
                @endif
            </span>
        </span>
        <span class="inv-ig__launcher-label">{{ $invCopy['stories_label'] ?? 'Ver como historia' }}</span>
    </button>

    <p class="sr-only" aria-live="polite" x-text="announce"></p>
</div>
