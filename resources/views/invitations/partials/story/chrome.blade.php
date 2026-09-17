{{--
    Controles del modo historia (resources/js/story/story.js): barra de progreso por escena, aviso de
    toque, botones de anterior/siguiente, «Ver todo» y el regreso desde la página completa.
    Las escenas son las partes que ya están en la página; sin JavaScript nada de esto se muestra.
    Estilos en resources/css/invitation/story.css.
--}}
<div class="inv-story" data-story x-data="invitationStory(@js(['enabled' => empty($isPreview)]))">
    <div class="inv-story__progress" x-show="on" x-cloak aria-hidden="true">
        <template x-for="position in count" :key="position">
            <span class="inv-story__segment" :class="{ 'is-done': position - 1 < index, 'is-current': position - 1 === index }"><i></i></span>
        </template>
    </div>

    <p class="inv-story__hint" x-show="on && index === 0 && !moved" x-cloak aria-hidden="true">
        <span class="inv-story__hint-ring"></span>
        {{ $storyHint ?? 'Toca para seguir' }}
    </p>

    <div class="inv-story__controls" x-show="on" x-cloak>
        <button type="button" class="inv-story__btn" @click="prev(true)" :disabled="index === 0" aria-label="Parte anterior">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M15 5l-7 7 7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>

        <span class="inv-story__count" aria-hidden="true"><span x-text="index + 1"></span>/<span x-text="count"></span></span>

        <button type="button" class="inv-story__btn inv-story__btn--next" @click="next(true)"
            :aria-label="index === count - 1 ? 'Volver a empezar' : 'Parte siguiente'">
            <svg x-show="index < count - 1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <svg x-show="index === count - 1" x-cloak viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 12a8 8 0 1 0 2.4-5.7M4 4v4h4" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>

        <button type="button" class="inv-story__all" @click="leave()">Ver todo</button>
    </div>

    <button type="button" class="inv-story__resume" x-show="ready && !on" x-cloak @click="resume()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="5" y="3" width="14" height="18" rx="2.5"/><path d="M10 9l4 3-4 3z" fill="currentColor" stroke="none"/></svg>
        Ver como historia
    </button>

    <p class="sr-only" aria-live="polite" x-text="announce"></p>
</div>

{{-- Pétalos que flotan detrás de los controles; se inclinan con el teléfono --}}
<div class="inv-petals" aria-hidden="true">
    @for($petal = 0; $petal < 9; $petal++)
        <span></span>
    @endfor
</div>
