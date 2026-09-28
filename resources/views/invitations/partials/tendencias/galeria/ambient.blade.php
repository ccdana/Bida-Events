{{--
    Fondo de «Galería Quince»: la sala de la exposición, armada en espejo. Del riel del techo cuelgan
    los focos: dos a los costados que barren la pared reflejados (cuando uno va hacia afuera el otro
    también) y, en pantallas anchas, uno al medio que solo respira. A los dos lados, las paredes de la
    sala con sus cuadros en pares: cada fila lleva un cuadro por pared a la misma altura y del mismo
    tamaño (marco de latón, paspartú, una obra con los colores de la paleta, su foquito y su cartela);
    pasan más despacio que el contenido al bajar, como si se caminara por la galería, y en el celular
    solo asoman por el borde. Cada tanto salta el flash de algún invitado, siempre en pares de lugares
    reflejados y a destiempo. Solo se anima transform y opacity. El polvo que flota en la luz lo pone
    el parcial de partículas. Estilos en tendencias/galeria.css. Lo incluye shell/themed-ambient.
--}}
@php
    // Una baldosa de pared de 960 px que se repite: por fila, altura, ancho, alto (px) y la obra de
    // cada pared (la izquierda y la derecha se miran, con obras distintas)
    $wallTile = 960;
    $wallRows = [
        [90, 84, 108, 1, 2],
        [420, 96, 72, 3, 3],
        [700, 72, 92, 2, 1],
    ];
@endphp
<div class="gq-ambient" aria-hidden="true">
    <div class="gq-ambient__walls" data-parallax="0.35" data-parallax-loop="{{ $wallTile }}">
        @foreach([0, $wallTile] as $tile)
            @foreach($wallRows as $row => [$top, $width, $height, $leftArt, $rightArt])
                @foreach(['left' => $leftArt, 'right' => $rightArt] as $side => $art)
                    <span class="gq-ambient__art gq-ambient__art--{{ $side }} is-art-{{ $art }}"
                        style="--top: {{ $tile + $top }}px; --w: {{ $width }}px; --h: {{ $height }}px; --i: {{ $row }}">
                        <i></i>
                    </span>
                @endforeach
            @endforeach
        @endforeach
    </div>

    <span class="gq-ambient__rail"></span>
    <span class="gq-ambient__spot gq-ambient__spot--left"><i></i></span>
    <span class="gq-ambient__spot gq-ambient__spot--center"><i></i></span>
    <span class="gq-ambient__spot gq-ambient__spot--right"><i></i></span>

    {{-- Flashes de los invitados: dos pares de lugares reflejados; saltan de a uno, alternando el lado --}}
    @foreach([[12, 30, 10.5], [88, 30, 7], [22, 74, 3.5], [78, 74, 0]] as [$x, $y, $delay])
        <span class="gq-ambient__flash" style="--x: {{ $x }}%; --y: {{ $y }}%; --delay: {{ -$delay }}s"></span>
    @endforeach
</div>
