{{--
    Fondo de «El cambio de zapatos»: los pasos del vals. Por cada costado, en espejo, una huella de
    tacón tras otra va apareciendo y se desvanece, subiendo en zigzag como quien baila (un compás de
    tres tiempos por huella: 0,6 s cada una, 3,6 s por recorrido completo). Arriba, la luz de la
    vitrina. El brillo que queda en el aire lo pone el parcial de partículas. Espera a que se abra la
    caja (.tr-scene). Solo transform y opacity. Estilos en tendencias/zapatos.css. Lo incluye
    shell/themed-ambient.
--}}
<div class="zp-ambient tr-scene" aria-hidden="true">
    <span class="zp-ambient__light"></span>
    @foreach(['left', 'right'] as $side)
        <span class="zp-ambient__trail zp-ambient__trail--{{ $side }}">
            @for($step = 0; $step < 6; $step++)
                <i class="zp-ambient__step" style="--n: {{ $step }}"></i>
            @endfor
        </span>
    @endforeach
</div>
