{{--
    Fondo de «Joyero musical»: el forro capitoné del joyero, muy tenue, que acompaña el scroll más
    despacio que el contenido (una baldosa que se repite: data-parallax-loop), con los botones del
    acolchado que destellan de a uno. Las chispas de oro las pone el parcial de partículas. Solo
    transform y opacity. Estilos en tendencias/joyero.css. Lo incluye shell/themed-ambient.
--}}
<div class="jo-ambient" aria-hidden="true">
    <span class="jo-ambient__quilt" data-parallax="0.2" data-parallax-loop="64"></span>
    @foreach([[14, 22, 0], [86, 22, 2.2], [26, 64, 4.4], [74, 64, 6.6], [50, 42, 8.8]] as [$x, $y, $delay])
        <span class="jo-ambient__glint" style="--x: {{ $x }}%; --y: {{ $y }}%; --delay: {{ $delay }}s"></span>
    @endforeach
</div>
