{{--
    Fondo de «Encomienda especial»: cajitas cerradas con su cinta y estampillas que bajan despacio,
    meciéndose, detrás del contenido. El papel picado de seda lo pone el parcial de partículas.
    Estilos en tendencias/encomienda.css. Lo incluye shell/themed-ambient.
--}}
@php
    // Pieza (caja o estampilla), posición (%), tamaño (rem), duración y retraso
    $ambientPieces = [
        ['box', 8, 2.2, 26, 0], ['stamp', 82, 1.6, 30, -9], ['box', 44, 1.5, 33, -18], ['stamp', 24, 1.3, 28, -4],
        ['box', 90, 1.9, 31, -23], ['stamp', 60, 1.5, 27, -13],
    ];
@endphp
<div class="en-ambient" aria-hidden="true">
    @foreach($ambientPieces as [$piece, $x, $size, $duration, $delay])
        <span class="en-ambient__fall" style="--x: {{ $x }}%; --size: {{ $size }}rem; --dur: {{ $duration }}s; --delay: {{ $delay }}s">
            <i class="en-ambient__{{ $piece }}"></i>
        </span>
    @endforeach
</div>
