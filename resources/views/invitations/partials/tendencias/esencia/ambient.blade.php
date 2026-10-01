{{--
    Fondo de «Esencia XV»: la bruma nacarada que dejó el atomizador. Tres nubes suaves (degradados ya
    difuminados: nada de filtros) que se desplazan muy despacio, en espejo. Cada 12 s el atomizador
    rocía desde las dos esquinas de arriba y, un compás después, sube por cada costado una estela de la
    fragancia (dos hilos de seda que ondulan). Las luces difusas las pone el parcial de partículas.
    Espera a que se abra la caja (.tr-scene). Solo transform y opacidad. Estilos en
    tendencias/esencia.css. Lo incluye shell/themed-ambient.
--}}
<div class="ez-ambient tr-scene" aria-hidden="true">
    <span class="ez-ambient__mist ez-ambient__mist--left"></span>
    <span class="ez-ambient__mist ez-ambient__mist--right"></span>
    <span class="ez-ambient__mist ez-ambient__mist--center"></span>
    @foreach(['left', 'right'] as $side)
        <span class="ez-ambient__spray ez-ambient__spray--{{ $side }}"></span>
        <svg class="ez-ambient__trail ez-ambient__trail--{{ $side }}" viewBox="0 0 200 400" preserveAspectRatio="none" focusable="false">
            <path d="M40 400 C150 320 -20 240 90 160 S150 40 110 0"/>
            <path d="M64 400 C174 322 4 238 112 158 S168 42 128 0"/>
        </svg>
    @endforeach
</div>
