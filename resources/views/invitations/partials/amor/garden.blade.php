{{--
    Capa viva de la tarjeta de amor (resources/css/cards/amor/garden.css):
    - el dibujo de flor que reusan la portada, el pasto y las respuestas (<use href="#amor-flower">);
    - un pasto al pie que florece a medida que se avanza (--story-visited, 0 a 1, lo pone story.js);
    - la ráfaga de pétalos que cruza la pantalla al cambiar de escena (html.is-turning);
    - el aviso de las mariposas encontradas (butterflyHunt en resources/js/cards/amor/garden.js).
    Todo es decorativo: sin JavaScript no se ve nada de esto salvo el dibujo reutilizado.
--}}
<svg class="inv-amor-sprite" width="0" height="0" aria-hidden="true" focusable="false">
    <symbol id="amor-flower" viewBox="0 0 40 40">
        @foreach([0, 60, 120, 180, 240, 300] as $angle)
            <ellipse cx="20" cy="10.5" rx="6" ry="9.5" transform="rotate({{ $angle }} 20 20)" style="fill: var(--flower-petal, currentColor); stroke: var(--flower-edge, transparent); stroke-width: 0.8" />
        @endforeach
        <circle cx="20" cy="20" r="5.6" style="fill: var(--flower-center, #f2c75c)" />
        <circle cx="18.4" cy="18.6" r="1.1" style="fill: var(--flower-dot, rgb(122 74 18 / 0.45))" />
        <circle cx="21.8" cy="20.8" r="0.9" style="fill: var(--flower-dot, rgb(122 74 18 / 0.45))" />
    </symbol>
</svg>

@php
    // Flores del pasto: posición, tamaño, color y desde qué parte del recorrido aparecen (--at)
    $meadow = [
        [4, 1.5, 'rose', 0], [11, 1.1, 'daisy', 0.1], [17, 1.8, 'sun', 0.25], [24, 1.2, 'rose', 0.4],
        [31, 1.5, 'daisy', 0.15], [38, 1.1, 'rose', 0.55], [45, 1.9, 'daisy', 0.3], [52, 1.3, 'sun', 0.7],
        [59, 1.6, 'rose', 0.2], [66, 1.1, 'daisy', 0.6], [73, 1.7, 'rose', 0.45], [80, 1.2, 'sun', 0.8],
        [87, 1.5, 'daisy', 0.35], [94, 1.3, 'rose', 0.9],
    ];
@endphp

<div class="inv-meadow" aria-hidden="true">
    <svg class="inv-meadow__grass" viewBox="0 0 400 60" preserveAspectRatio="none">
        <path d="M0 60 L0 38 Q10 22 16 38 Q24 18 32 36 Q40 24 48 38 Q58 16 66 36 Q76 26 84 38 Q94 14 102 36 Q112 24 120 38 Q130 20 138 36 Q148 26 156 38 Q166 16 174 36 Q184 24 192 38 Q202 18 210 36 Q220 26 228 38 Q238 14 246 36 Q256 24 264 38 Q274 20 282 36 Q292 26 300 38 Q310 16 318 36 Q328 24 336 38 Q346 18 354 36 Q364 26 372 38 Q382 16 390 36 L400 38 L400 60 Z" />
    </svg>
    @foreach($meadow as [$left, $size, $tone, $at])
        <span class="inv-meadow__flower inv-meadow__flower--{{ $tone }}" style="--left: {{ $left }}%; --size: {{ $size }}rem; --at: {{ $at }}; --i: {{ $loop->index }}">
            <i class="inv-meadow__stem"></i>
            <svg viewBox="0 0 40 40"><use href="#amor-flower" /></svg>
        </span>
    @endforeach
</div>

{{-- Ráfaga de pétalos entre escenas --}}
<div class="inv-sweep" aria-hidden="true">
    @for($petal = 0; $petal < 16; $petal++)
        <i style="--i: {{ $petal }}"></i>
    @endfor
</div>

{{-- Mariposas encontradas --}}
<div class="inv-hunt" x-data="butterflyHunt({ total: 3, message: @js($invCopy['butterflies_found'] ?? 'Encontraste las tres mariposas.') })" x-cloak>
    <p class="inv-hunt__toast" x-show="toast" x-transition.opacity.duration.400ms role="status" aria-live="polite">
        <svg viewBox="0 0 48 40" aria-hidden="true"><path d="M23 20 C 14 2, 1 4, 3 15 C 4 22, 14 22, 23 20 Z M25 20 C 34 2, 47 4, 45 15 C 44 22, 34 22, 25 20 Z M23 21 C 12 24, 5 32, 11 36 C 17 39, 22 30, 23 21 Z M25 21 C 36 24, 43 32, 37 36 C 31 39, 26 30, 25 21 Z" /></svg>
        <span x-text="toast"></span>
    </p>
</div>
