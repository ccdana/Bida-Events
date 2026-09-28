{{--
    Fondo de «Mapa de estrellas»: el cielo gira muy despacio alrededor de un polo, arriba, y cada tanto
    pasa una estrella fugaz. Valores deterministas; estilos en css/invitation/tendencias/estrellas.css.
    Lo incluye shell/themed-ambient.
--}}
@php
    $seed = crc32('estrellas-fondo');
    $next = function () use (&$seed): float {
        $seed = ($seed * 1103515245 + 12345) & 0x7fffffff;

        return $seed / 0x7fffffff;
    };
@endphp
<div class="es-ambient" aria-hidden="true">
    <svg class="es-ambient__sky" viewBox="0 0 1000 1000" focusable="false">
        @for($star = 0; $star < 110; $star++)
            <circle cx="{{ round($next() * 1000) }}" cy="{{ round($next() * 1000) }}" r="{{ round(0.5 + $next() * $next() * 1.3, 1) }}" style="--delay: -{{ round($next() * 5, 1) }}s"/>
        @endfor
    </svg>
    <span class="es-ambient__shooting es-ambient__shooting--1"></span>
    <span class="es-ambient__shooting es-ambient__shooting--2"></span>
</div>
