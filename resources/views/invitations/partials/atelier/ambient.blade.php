{{--
    Fondo de «Atelier»: el taller. A cada lado cuelga una cinta métrica que se mece despacio, en espejo
    (cuando una va hacia afuera, la otra también); en el celular solo asoman por el borde. Entre las
    cintas bajan retazos cortados con tijera de zigzag, de a pares en espejo (la tela de la casa y el
    satén), tres vaivenes por caída. Los hilos sueltos los pone el parcial de partículas. Espera a que
    se abra la funda (.tr-scene). Solo transform. Estilos en themes/atelier.css. Lo incluye
    shell/themed-ambient.
--}}
<div class="at-ambient tr-scene" aria-hidden="true">
    <span class="at-ambient__tape at-ambient__tape--left"></span>
    <span class="at-ambient__tape at-ambient__tape--right"></span>
    @foreach([['weave', 0, '0s'], ['satin', 1, '-13.5s']] as [$fabric, $lane, $delay])
        @foreach(['left', 'right'] as $side)
            <span class="at-ambient__scrap at-ambient__scrap--{{ $side }} at-ambient__scrap--{{ $fabric }}" style="--lane: {{ $lane }}; --delay: {{ $delay }}"></span>
        @endforeach
    @endforeach
</div>
