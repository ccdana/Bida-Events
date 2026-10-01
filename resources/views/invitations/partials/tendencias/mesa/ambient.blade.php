{{--
    Fondo de «Mesa de honor»: la luz de las velas de la mesa. A los dos lados, en espejo, el resplandor
    cálido de una vela que tiembla (solo opacidad y escala). Desde la tableta, las dos velas en sus
    candeleros de oro, con la llama que se mece y su halo que late con ella (1,6 s: el resplandor tarda
    dos y los pétalos, dieciséis). Pétalos del ramo que bajan despacio por los costados, de a pares en
    espejo. Las luces difusas las pone el parcial de partículas. Espera a que se abra la servilleta
    (.tr-scene). Estilos en tendencias/mesa.css. Lo incluye shell/themed-ambient.
--}}
<div class="ms-ambient tr-scene" aria-hidden="true">
    <span class="ms-ambient__glow ms-ambient__glow--left"></span>
    <span class="ms-ambient__glow ms-ambient__glow--right"></span>
    @foreach(['left', 'right'] as $side)
        <span class="ms-ambient__candle ms-ambient__candle--{{ $side }}">
            <i class="ms-ambient__halo"></i>
            <i class="ms-ambient__flame"></i>
        </span>
    @endforeach
    @foreach([[0, '0s'], [1, '-12.8s']] as [$lane, $delay])
        @foreach(['left', 'right'] as $side)
            <span class="ms-ambient__petal ms-ambient__petal--{{ $side }}" style="--lane: {{ $lane }}; --delay: {{ $delay }}"></span>
        @endforeach
    @endforeach
</div>
