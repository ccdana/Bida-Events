{{--
    La invitación en modo historia (resources/js/story/story.js con optIn): cada parte de la página
    es una escena a pantalla completa, como la tarjeta del Día del Amor. Solo cambia de escena cuando
    el invitado toca, desliza o usa las flechas; la X (o «Ver todo» en el menú) vuelve a la página.
    Cada plantilla tiene su transición y su detalle al pasar (invitation/story.css, «Historia de cada
    plantilla»). Se abre con el botón de la esquina, desde el menú o con ?historias en el enlace.
    Necesita $page.
--}}
@vite('resources/css/invitation/story.css')

@php
    // Lo que dice el aviso del primer toque, en el tono de cada plantilla
    $storyHint = [
        'boda' => 'Toca para pasar la página',
        'xv' => 'Toca para seguir la noche',
        'bautizo' => 'Toca para seguir',
        'cumple' => 'Toca para seguir la fiesta',
        'graduacion' => 'Toca para seguir leyendo',
        'halloween' => 'Toca si te atreves',
    ][$page->eventKey] ?? 'Toca para seguir';
@endphp

<div class="inv-tale" data-inv-story data-theme="{{ $page->theme }}"
    x-data="invitationStory(@js(['enabled' => empty($isPreview), 'optIn' => true]))"
    @scroll.window.throttle.150ms="atTop = window.scrollY < window.innerHeight * 0.6">

    <div class="inv-story__progress" x-show="on" x-cloak aria-hidden="true">
        <template x-for="position in count" :key="position">
            <span class="inv-story__segment" :class="{ 'is-done': position - 1 < index, 'is-current': position - 1 === index }"><i></i></span>
        </template>
    </div>

    <button type="button" class="inv-tale__close" x-show="on" x-cloak @click="leave()" aria-label="Cerrar la historia y ver la página completa">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
    </button>

    <p class="inv-story__hint" x-show="on && index === 0 && !moved" x-cloak aria-hidden="true">
        <span class="inv-story__hint-ring"></span>
        {{ $invCopy['stories_hint'] ?? $storyHint }}
    </p>

    {{-- Anterior, cuántas van y siguiente: visibles en la computadora, en el celular se toca la escena --}}
    <div class="inv-tale__controls" x-show="on" x-cloak>
        <button type="button" class="inv-story__btn" @click="prev(true)" :disabled="index === 0" aria-label="Parte anterior">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M15 5l-7 7 7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
        <span class="inv-story__count" aria-hidden="true"><span x-text="index + 1"></span>/<span x-text="count"></span></span>
        <button type="button" class="inv-story__btn inv-story__btn--next" @click="next(true)"
            :aria-label="index === count - 1 ? 'Volver a empezar' : 'Parte siguiente'">
            <svg x-show="index < count - 1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <svg x-show="index === count - 1" x-cloak viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 12a8 8 0 1 0 2.4-5.7M4 4v4h4" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
    </div>

    {{-- El detalle de cada plantilla al pasar de escena (hojas, destellos, confeti, nubes, murciélagos…) --}}
    <div class="inv-tale__turn" aria-hidden="true">
        @for($piece = 0; $piece < 14; $piece++)
            <i style="{{ sprintf('--x:%.1f%%;--y:%.1f%%;--d:%dms;--r:%ddeg;--s:%.2f', fmod($piece * 37.7 + 6, 94), fmod($piece * 53.3 + 9, 88), ($piece * 47) % 320, (($piece * 71) % 120) - 60, 0.6 + ($piece % 4) * 0.2) }}"></i>
        @endfor
    </div>

    {{-- El botón para verla como historia, con el color de la plantilla --}}
    <button type="button" class="inv-tale__launcher" x-show="ready && !on && atTop" x-cloak x-transition.opacity.duration.300ms @click="start()">
        <span class="inv-tale__launcher-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="6" y="3" width="12" height="18" rx="2.5"/><path d="M3 6v12M21 6v12"/><path d="M10.5 9.5v5l4-2.5z" fill="currentColor" stroke="none"/></svg>
        </span>
        <span class="inv-tale__launcher-label">{{ $invCopy['stories_label'] ?? 'Ver como historia' }}</span>
    </button>

    <p class="sr-only" aria-live="polite" x-text="announce"></p>
</div>
