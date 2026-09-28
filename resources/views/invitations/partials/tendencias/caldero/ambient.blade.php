{{--
    Fondo de «Caldero encantado»: una bruma de colores que se arrastra por el piso del laboratorio,
    burbujas que suben desde abajo y revientan antes de llegar arriba, y lucecitas de colores que
    flotan. Nada asusta. Estilos en css/invitation/tendencias/caldero.css. Lo incluye
    shell/themed-ambient.
--}}
@php
    // Burbujas: posición, tamaño (rem), duración, retraso y color (1 principal, 2 secundario)
    $ambientBubbles = [
        [8, 1.1, 14, 0, 1], [18, 0.6, 11, -6, 2], [31, 0.9, 16, -3, 2], [46, 0.5, 12, -9, 1], [58, 1.3, 18, -12, 2],
        [69, 0.7, 13, -2, 1], [81, 1, 15, -7, 2], [92, 0.55, 12, -10, 1],
    ];
@endphp
<div class="cl-ambient" aria-hidden="true">
    <span class="cl-ambient__mist cl-ambient__mist--1"></span>
    <span class="cl-ambient__mist cl-ambient__mist--2"></span>
    @foreach($ambientBubbles as [$x, $size, $duration, $delay, $tone])
        <i class="cl-ambient__bubble is-tone-{{ $tone }}" style="--x: {{ $x }}%; --size: {{ $size }}rem; --dur: {{ $duration }}s; --delay: {{ $delay }}s"></i>
    @endforeach
</div>
