{{--
    Fondo de «Cuento desplegable»: el libro abierto alrededor de la página. A los dos lados, el canto de
    las hojas (en el celular solo una franja fina) y, arriba, dos guirnaldas de papel recortado que se
    mecen en espejo. Desde arriba baja un haz de luz sobre el libro, con motas de polvo dorado que
    flotan en él. Abajo, el escenario del desplegable: las colinas, una torre del salón con su árbol a
    cada costado (en espejo) y los arbustos de adelante, que se ponen de pie capa por capa cuando se
    abre el libro y después se inclinan apenas, al mismo compás, como el papel cuando se mueve la
    página. Con música, el escenario se apoya sobre el reproductor. Las
    estrellitas de papel las pone el parcial de partículas. Espera a que se abra el libro (.tr-scene).
    Solo transform y opacity. Estilos en tendencias/cuento.css. Lo incluye shell/themed-ambient.
--}}
<div class="cu-ambient tr-scene" aria-hidden="true">
    <span class="cu-ambient__beam"></span>
    @foreach([[34, 18, '0s'], [66, 18, '0s'], [42, 34, '-2.3s'], [58, 34, '-2.3s'], [30, 50, '-4.6s'], [70, 50, '-4.6s'], [46, 62, '-1.2s'], [54, 62, '-1.2s']] as [$x, $y, $delay])
        <span class="cu-ambient__mote" style="--x: {{ $x }}%; --y: {{ $y }}%; --delay: {{ $delay }}; --dir: {{ $x < 50 ? -1 : 1 }}"></span>
    @endforeach

    <span class="cu-ambient__edge cu-ambient__edge--left"></span>
    <span class="cu-ambient__edge cu-ambient__edge--right"></span>
    <span class="cu-ambient__garland cu-ambient__garland--left"></span>
    <span class="cu-ambient__garland cu-ambient__garland--right"></span>

    <div class="cu-ambient__stage">
        <span class="cu-ambient__layer cu-ambient__layer--hills"></span>
        {{-- Una torre del salón con su muralla y un árbol en cada costado (la de la derecha, en espejo):
             el centro lo ocupa la página, así el escenario se ve junto al contenido --}}
        <span class="cu-ambient__layer cu-ambient__layer--hall">
            @foreach(['left', 'right'] as $side)
                <svg class="cu-ambient__side cu-ambient__side--{{ $side }}" viewBox="0 0 150 110" focusable="false">
                    <path class="cu-ambient__tree" d="M112 110 V92 H105 L120 56 L135 92 H128 V110 Z"/>
                    <path class="cu-ambient__hall" d="M30 110 V48 H25 L40 26 L55 48 H50 V70 H56 V64 H62 V70 H68 V64 H74 V70 H80 V64 H86 V70 H92 V110 Z"/>
                    <path class="cu-ambient__flag" d="M40 26 V13 L51 17 L40 21"/>
                    <path class="cu-ambient__window" d="M36 60 A4 4 0 0 1 44 60 V70 H36 Z M62 110 V92 A9 9 0 0 1 80 92 V110 Z"/>
                </svg>
            @endforeach
        </span>
        <span class="cu-ambient__layer cu-ambient__layer--bushes"></span>
    </div>
</div>
