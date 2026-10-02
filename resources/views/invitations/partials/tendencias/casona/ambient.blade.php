{{--
    Fondo de «Casa de muñecas de medianoche»: la noche alrededor de la casa (la luna va detrás del
    tejado de la portada). A cada costado, en espejo, un murciélago que va y viene aleteando y una araña
    que baja por su hilo y vuelve a subir; abajo, la neblina del jardín que se corre despacio. Un ciclo de 12 s (el
    aleteo, 0,5 s; la araña, 6 s; los murciélagos, 12 s). Espera a que se abra la casa (.tr-scene).
    Solo transform y opacity. Estilos en tendencias/casona.css. Lo incluye shell/themed-ambient.
--}}
<div class="cs-ambient tr-scene" aria-hidden="true">
    @foreach(['left', 'right'] as $side)
        <span class="cs-ambient__flight cs-ambient__flight--{{ $side }}">
            @include('invitations.partials.tendencias.casona.bat', ['class' => 'cs-ambient__bat'])
        </span>
        <span class="cs-ambient__spider cs-ambient__spider--{{ $side }}">
            <i class="cs-ambient__thread"></i>
            <svg class="cs-ambient__body" viewBox="0 0 30 24" focusable="false">
                <path class="cs-ambient__legs" d="M10 10 L3 5 L1 9 M10 13 L2 13 L0 17 M11 15 L5 20 L4 24 M20 10 L27 5 L29 9 M20 13 L28 13 L30 17 M19 15 L25 20 L26 24"/>
                <ellipse class="cs-ambient__abdomen" cx="15" cy="13" rx="6" ry="7"/>
                <circle class="cs-ambient__eye" cx="13" cy="11" r="1.4"/>
                <circle class="cs-ambient__eye" cx="17" cy="11" r="1.4"/>
            </svg>
        </span>
    @endforeach
    <span class="cs-ambient__fog cs-ambient__fog--back"></span>
    <span class="cs-ambient__fog cs-ambient__fog--front"></span>
</div>
