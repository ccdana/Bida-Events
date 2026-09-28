{{--
    Fondo de «Bordado a mano»: por los dos bordes de la pantalla corre un pespunte que no para de
    avanzar, como si alguien siguiera cosiendo el lino, y unos botones de costura bajan despacio
    girando detrás del contenido. Estilos en css/invitation/tendencias/bordado.css. Lo incluye
    shell/themed-ambient.
--}}
@php
    // Botones: posición, tamaño, duración, retraso y color (1 = hilo del nombre, 2 = flores, 3 = madera)
    $ambientButtons = [
        [6, 1.5, 26, 0, 1], [88, 1.1, 31, -9, 2], [22, 0.9, 34, -21, 3],
        [70, 1.35, 29, -15, 1], [46, 1, 37, -4, 2], [94, 0.8, 33, -26, 3],
    ];
@endphp
<div class="bd-ambient" aria-hidden="true">
    <span class="bd-ambient__seam bd-ambient__seam--left"></span>
    <span class="bd-ambient__seam bd-ambient__seam--right"></span>
    @foreach($ambientButtons as [$x, $size, $duration, $delay, $tone])
        <span class="bd-ambient__fall" style="--x: {{ $x }}%; --size: {{ $size }}; --dur: {{ $duration }}s; --delay: {{ $delay }}s">
            @include('invitations.partials.tendencias.bordado.button', ['class' => "bd-ambient__button is-tone-{$tone}"])
        </span>
    @endforeach
</div>
