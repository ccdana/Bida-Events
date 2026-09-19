{{--
    Lluvia de flores de la tarjeta «Sobre lacrado» (resources/css/cards/sobre/fall.css):
    - el dibujo de flor que reusan la lluvia, la carta y la respuesta (<use href="#sobre-flower">);
    - una caída suave y constante de flores sobre toda la página;
    - la ráfaga fuerte al abrir el sobre y una pequeña en cada cambio de escena
      (flowerRain en resources/js/cards/sobre/flowers.js, que escucha el evento «inv-flowers»).
    Todo es decorativo: sin JavaScript solo queda la caída suave del CSS.
--}}
<svg class="inv-sobre-sprite" width="0" height="0" aria-hidden="true" focusable="false">
    <symbol id="amor-flower" viewBox="0 0 40 40">
        @foreach([0, 72, 144, 216, 288] as $angle)
            <ellipse cx="20" cy="9.5" rx="6.4" ry="10" transform="rotate({{ $angle }} 20 20)" style="fill: var(--flower-petal, currentColor)" />
        @endforeach
        <circle cx="20" cy="20" r="5.4" style="fill: var(--flower-center, #f6d98c)" />
        <circle cx="18.3" cy="18.7" r="1" style="fill: var(--flower-dot, rgb(122 74 18 / 0.4))" />
        <circle cx="21.7" cy="20.9" r="0.8" style="fill: var(--flower-dot, rgb(122 74 18 / 0.4))" />
    </symbol>

    <symbol id="amor-heart" viewBox="0 0 24 22">
        <path d="M12 21S3 15.2 1 10C-.6 5.8 2 1 6.5 1c2.4 0 4 1.4 5.5 3.3C13.5 2.4 15.1 1 17.5 1 22 1 24.6 5.8 23 10c-2 5.2-11 11-11 11z" style="fill: var(--flower-petal, currentColor)" />
    </symbol>
</svg>

@php
    // Caída constante: posición, tamaño, demora, duración, giro y tono de cada flor
    $rain = [
        [6, 1.5, 0, 15, 1], [14, 1.1, 5, 19, 2], [23, 1.8, 9, 16, 0], [31, 1.2, 2, 21, 1],
        [39, 1.4, 12, 17, 2], [47, 1.0, 7, 22, 0], [55, 1.7, 3, 18, 1], [63, 1.2, 14, 20, 2],
        [71, 1.5, 6, 16, 0], [79, 1.1, 11, 23, 1], [87, 1.6, 1, 19, 2], [94, 1.3, 8, 17, 0],
    ];
@endphp

<div class="inv-fall" data-flower-rain aria-hidden="true">
    @foreach($rain as [$left, $size, $delay, $duration, $tone])
        <span class="inv-fall__flower" data-tone="{{ $tone }}"
            style="--left: {{ $left }}%; --size: {{ $size }}rem; --delay: {{ $delay }}s; --duration: {{ $duration }}s; --spin: {{ $loop->index % 2 ? 1 : -1 }}">
            <svg viewBox="0 0 40 40"><use href="#amor-flower" /></svg>
        </span>
    @endforeach
</div>
