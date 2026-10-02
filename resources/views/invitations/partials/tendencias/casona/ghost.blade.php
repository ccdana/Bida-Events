{{--
    El fantasma amable de «Casa de muñecas de medianoche»: una sábana con dos ojos, la boquita de
    sorpresa y los cachetes con rubor. Nada de miedo. Colores en tendencias/casona.css (la sábana con
    el acento aclarado, los ojos con el texto oscurecido). class: clases extra.
--}}
<svg class="cs-ghost {{ $class ?? '' }}" viewBox="0 0 60 72" aria-hidden="true" focusable="false">
    <path class="cs-ghost__sheet" d="M30 4 C15 4 7 16 7 31 V60 Q7 68 13 64 Q18 59 22 65 Q26 70 30 65 Q34 70 38 65 Q42 59 47 64 Q53 68 53 60 V31 C53 16 45 4 30 4 Z"/>
    <path class="cs-ghost__shade" d="M44 12 C51 19 53 28 53 36 V58 C50 52 46 50 44 50 Z"/>
    <ellipse class="cs-ghost__eye" cx="23" cy="30" rx="3" ry="4.2"/>
    <ellipse class="cs-ghost__eye" cx="37" cy="30" rx="3" ry="4.2"/>
    <circle class="cs-ghost__cheek" cx="18.5" cy="37.5" r="2.8"/>
    <circle class="cs-ghost__cheek" cx="41.5" cy="37.5" r="2.8"/>
    <ellipse class="cs-ghost__mouth" cx="30" cy="40" rx="2.4" ry="2.9"/>
</svg>
