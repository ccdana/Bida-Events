{{--
    Fondo de «Galería Quince»: la sala de la exposición. Tres focos colgados del riel barren la pared
    despacio, cada uno a su ritmo, y donde pega su luz se ve el círculo del haz; el polvo que flota en
    esa luz lo pone el parcial de partículas. Estilos en tendencias/galeria.css. Lo incluye
    shell/themed-ambient.
--}}
<div class="gq-ambient" aria-hidden="true">
    <span class="gq-ambient__rail"></span>
    @foreach([[18, 13, -2], [52, 17, -9], [84, 15, -5]] as $index => [$x, $duration, $delay])
        <span @class(['gq-ambient__spot', 'is-extra' => $index === 1]) style="--x: {{ $x }}%; --d: {{ $duration }}s; --delay: {{ $delay }}s">
            <i></i>
        </span>
    @endforeach
</div>
