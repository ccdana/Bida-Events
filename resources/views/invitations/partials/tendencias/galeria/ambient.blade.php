{{--
    Fondo de «Galería Quince»: la sala de la exposición. Tres focos colgados del riel barren la pared
    despacio, cada uno a su ritmo, y donde pega su luz se ve el círculo del haz. A los costados, las
    paredes de la sala con sus cuadros (marco de latón, paspartú y una obra con los colores de la
    paleta, cada una con su foquito y su cartela) pasan más despacio que el contenido al bajar, como
    si se caminara por la galería; en el celular solo asoman por el borde. Cada tanto salta el flash
    de algún invitado. El polvo que flota en la luz lo pone el parcial de partículas.
    Estilos en tendencias/galeria.css. Lo incluye shell/themed-ambient.
--}}
@php
    // Una baldosa de pared de 960 px que se repite: lado, altura, ancho, alto (px) y cuál de las obras
    $wallTile = 960;
    $wallArt = [
        ['left', 70, 88, 112, 1], ['right', 250, 76, 96, 2], ['left', 450, 70, 70, 3],
        ['right', 610, 96, 72, 1], ['left', 760, 64, 84, 2],
    ];
@endphp
<div class="gq-ambient" aria-hidden="true">
    <div class="gq-ambient__walls" data-parallax="0.35" data-parallax-loop="{{ $wallTile }}">
        @foreach([0, $wallTile] as $tile)
            @foreach($wallArt as $index => [$side, $top, $width, $height, $art])
                <span class="gq-ambient__art gq-ambient__art--{{ $side }} is-art-{{ $art }}"
                    style="--top: {{ $tile + $top }}px; --w: {{ $width }}px; --h: {{ $height }}px; --i: {{ $index }}">
                    <i></i>
                </span>
            @endforeach
        @endforeach
    </div>

    <span class="gq-ambient__rail"></span>
    @foreach([[18, 13, -2], [52, 17, -9], [84, 15, -5]] as $index => [$x, $duration, $delay])
        <span @class(['gq-ambient__spot', 'is-extra' => $index === 1]) style="--x: {{ $x }}%; --d: {{ $duration }}s; --delay: {{ $delay }}s">
            <i></i>
        </span>
    @endforeach

    {{-- Flashes de los invitados, a destiempo --}}
    @foreach([[14, 30, 11, -3], [82, 58, 14, -9], [30, 78, 17, -6], [70, 16, 19, -14]] as [$x, $y, $duration, $delay])
        <span class="gq-ambient__flash" style="--x: {{ $x }}%; --y: {{ $y }}%; --d: {{ $duration }}s; --delay: {{ $delay }}s"></span>
    @endforeach
</div>
