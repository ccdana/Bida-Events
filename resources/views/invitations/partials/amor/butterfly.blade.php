{{--
    Una de las tres mariposas escondidas de la tarjeta de amor. Revolotea cerca de su lugar
    (--bx, --by dentro de la escena) y al tocarla sale volando; butterflyHunt (partials/amor/garden)
    cuenta cuántas se encontraron. Parámetros: butterflyId y butterflyStyle.
--}}
<button type="button" class="inv-butterfly" data-butterfly="{{ $butterflyId }}" style="{{ $butterflyStyle ?? '' }}"
    x-data @click="$dispatch('butterfly-caught', { id: @js($butterflyId), rect: $el.getBoundingClientRect() })"
    aria-label="Una mariposa escondida: tócala">
    <span class="inv-butterfly__flight" aria-hidden="true">
        <svg class="inv-butterfly__art" viewBox="0 0 48 40">
            <g class="inv-butterfly__wing inv-butterfly__wing--left">
                <path d="M23 20 C 14 2, 1 4, 3 15 C 4 22, 14 22, 23 20 Z" />
                <path d="M23 21 C 12 24, 5 32, 11 36 C 17 39, 22 30, 23 21 Z" />
                <circle cx="10" cy="13" r="2.4" />
            </g>
            <g class="inv-butterfly__wing inv-butterfly__wing--right">
                <path d="M25 20 C 34 2, 47 4, 45 15 C 44 22, 34 22, 25 20 Z" />
                <path d="M25 21 C 36 24, 43 32, 37 36 C 31 39, 26 30, 25 21 Z" />
                <circle cx="38" cy="13" r="2.4" />
            </g>
            <path class="inv-butterfly__body" d="M24 11 C 25.5 16, 25.5 26, 24 31 C 22.5 26, 22.5 16, 24 11 Z" />
            <path class="inv-butterfly__antenna" d="M23.5 12 C 22 8, 20 6, 18 5 M24.5 12 C 26 8, 28 6, 30 5" />
        </svg>
    </span>
</button>
