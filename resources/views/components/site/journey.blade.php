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
        ? [1 => __('Cuenta'), 2 => __('Tu invitación'), 3 => __('Publícala')]
        : [1 => __('Plan'), 2 => __('Cuenta'), 3 => __('Pago'), 4 => __('Tu invitación')];
@endphp

<ol {{ $attributes->class('site-journey') }} aria-label="{{ __('Pasos para crear tu invitación') }}">
    @foreach($journey as $number => $label)
        <li @class(['is-done' => $number < $current, 'is-current' => $number === $current]) @if($number === $current) aria-current="step" @endif>
            @if($number < $current)
                <x-phosphor-check-bold class="site-journey__check" aria-hidden="true" />
            @endif
            <span class="sr-only">{{ __('Paso :number de :value:', ['number' => $number, 'value' => count($journey)]) }} </span>{{ $label }}
        </li>
    @endforeach
</ol>
