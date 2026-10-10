{{--
    Los cuatro pasos de quien arma su propia invitación («Hazlo tú»): elegir el plan, crear la cuenta
    (con su correo confirmado), pagar y crear la invitación. Se muestra en el registro, al confirmar
    el correo, al pagar y en el panel vacío, para que siempre se sepa en qué paso se está y qué sigue.
    «current» es el paso de hoy (1 a 4): los anteriores van marcados como hechos. Estilos: home.css.
--}}
@props(['current' => 1])

@php
    $journey = [1 => 'Plan', 2 => 'Cuenta', 3 => 'Pago', 4 => 'Tu invitación'];
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
