{{--
    Fondo de «Joyero musical»: el forro capitoné del joyero, muy tenue, que acompaña el scroll más
    despacio que el contenido (una baldosa que se repite: data-parallax-loop), con los botones del
    acolchado que destellan de a uno. Todo va al compás de un vals (un compás de tres tiempos = 2,1 s):
    los botones destellan cada seis compases, las perlas sueltas suben por los costados en espejo en
    ocho y, mientras suena la música (html.inv-music-on), desde las dos esquinas de abajo salen ondas
    de oro, una por tiempo, más marcada en el primero. Las chispas de oro las pone el parcial de
    partículas. Espera a que se abra el joyero (.tr-scene). Solo transform y opacity. Estilos en
    tendencias/joyero.css. Lo incluye shell/themed-ambient.
--}}
<div class="jo-ambient tr-scene" aria-hidden="true">
    <span class="jo-ambient__quilt" data-parallax="0.2" data-parallax-loop="64"></span>
    @foreach([[14, 22, 0], [86, 22, 2.1], [26, 64, 4.2], [74, 64, 6.3], [50, 42, 8.4]] as [$x, $y, $delay])
        <span class="jo-ambient__glint" style="--x: {{ $x }}%; --y: {{ $y }}%; --delay: {{ $delay }}s"></span>
    @endforeach
    @foreach([[0, 1, '0s'], [1, 0.75, '-5.6s'], [2, 0.9, '-11.2s']] as [$lane, $size, $delay])
        @foreach(['left', 'right'] as $side)
            <span class="jo-ambient__pearl jo-ambient__pearl--{{ $side }}" style="--lane: {{ $lane }}; --s: {{ $size }}; --delay: {{ $delay }}"></span>
        @endforeach
    @endforeach
    @foreach(['left', 'right'] as $side)
        <span class="jo-ambient__rings jo-ambient__rings--{{ $side }}">
            <i style="--n: 0"></i><i style="--n: 1"></i><i style="--n: 2"></i>
        </span>
    @endforeach
</div>
