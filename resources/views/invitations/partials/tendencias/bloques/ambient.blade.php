{{--
    Fondo de «Bloques de juguete»: bloquecitos con letras y números que caen despacio dando vueltas,
    como si alguien los fuera soltando, detrás del contenido. Estilos en tendencias/bloques.css. Lo
    incluye shell/themed-ambient.
--}}
@php
    // Letra, posición (%), tamaño (rem), duración, retraso y color
    $ambientBlocks = [
        ['A', 6, 1.9, 21, 0, 0], ['B', 84, 1.5, 25, -8, 1], ['C', 30, 1.2, 28, -15, 2], ['1', 62, 1.7, 23, -4, 0],
        ['2', 16, 1.3, 26, -19, 1], ['3', 92, 1.1, 30, -11, 2], ['★', 46, 1.4, 24, -2, 2], ['D', 74, 1.2, 27, -21, 0],
    ];
@endphp
<div class="bl-ambient" aria-hidden="true">
    @foreach($ambientBlocks as [$letter, $x, $size, $duration, $delay, $tone])
        <span class="bl-ambient__fall" style="--x: {{ $x }}%; --size: {{ $size }}rem; --dur: {{ $duration }}s; --delay: {{ $delay }}s">
            @include('invitations.partials.tendencias.bloques.block', ['letter' => $letter, 'tone' => $tone, 'class' => 'bl-ambient__block'])
        </span>
    @endforeach
</div>
