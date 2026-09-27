{{--
    Una figura de fieltro de «Móvil de cuna»: la forma rellena y, encima, la costura (la misma forma
    un poco más chica, con puntada). Parámetros: shape (star, moon, house, candle, lamb, heart) y
    class (el color de fieltro: mv-felt--1, --2 o --3).
--}}
<svg class="mv-figure {{ $class ?? '' }}" viewBox="0 0 100 100" aria-hidden="true" focusable="false">
    <use href="#mv-{{ $shape }}" class="mv-figure__felt"/>
    <use href="#mv-{{ $shape }}" class="mv-figure__stitch"/>
</svg>
