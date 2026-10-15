{{--
    Los pasos de quien arma su propia invitación, para que siempre sepa en qué paso está y qué sigue.
    Dos recorridos («flow»):
    - diy (Hazlo tú, el principal): crear la cuenta, armar la invitación y publicarla (gratis o con
      el nivel que usó). Se muestra en el registro y al confirmar el correo.
    - plan (Únete, para quien arma muchas): elegir el plan, crear la cuenta (con su correo confirmado),
      pagar y crear la invitación. Se muestra en el registro con plan, al pagar y en el panel del plan.
    «current» es el paso de hoy: los anteriores van marcados como hechos. Estilos: home.css.
--}}
@props(['current' => 1, 'flow' => 'plan'])

@php
    $journey = $flow === 'diy'
        ? [1 => 'Cuenta', 2 => 'Tu invitación', 3 => 'Publícala']
        : [1 => 'Plan', 2 => 'Cuenta', 3 => 'Pago', 4 => 'Tu invitación'];
@endphp

<ol {{ $attributes->class('site-journey') }} aria-label="Pasos para crear tu invitación">
    @foreach($journey as $number => $label)
        <li @class(['is-done' => $number < $current, 'is-current' => $number === $current]) @if($number === $current) aria-current="step" @endif>
            @if($number < $current)
                <x-phosphor-check-bold class="site-journey__check" aria-hidden="true" />
            @endif
            <span class="sr-only">Paso {{ $number }} de {{ count($journey) }}: </span>{{ $label }}
        </li>
    @endforeach
</ol>
