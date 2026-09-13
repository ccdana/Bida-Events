{{--
    Isotipo de Bida Events: un celular cuya pantalla se cierra como la solapa
    de un sobre (la invitación que llega al teléfono). Vectorizado a partir del
    logo original; los trazos usan currentColor y el grosor se ajusta con la
    variable --brand-stroke para que se lea bien en tamaños pequeños.
--}}
@props(['animated' => false])

@php($maskId = 'brand-mask-'.\Illuminate\Support\Str::random(6))

<svg viewBox="355 128 544 966" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"
    {{ $attributes->class(['brand-mark', 'brand-mark--animated' => $animated]) }}>
    <mask id="{{ $maskId }}" maskUnits="userSpaceOnUse" x="355" y="128" width="544" height="966">
        <rect x="355" y="128" width="544" height="966" fill="#fff" />
        {{-- Separación entre la solapa y el cuerpo del celular --}}
        <path class="brand-mark__gap" d="M380 322.5 627 541l247-218.5" stroke="#000" />
    </mask>
    <g stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
        <path class="brand-mark__line" style="--i: 0" pathLength="1" mask="url(#{{ $maskId }})"
            d="M419 357v635a78 78 0 0 0 78 78h260a78 78 0 0 0 78-78V357" />
        <path class="brand-mark__line brand-mark__flap" style="--i: 2" pathLength="1"
            d="M419 357V230a78 78 0 0 1 78-78h260a78 78 0 0 1 78 78v127L627 541Z" />
        <path class="brand-mark__line" style="--i: 4" pathLength="1" d="M581 216h93" />
        <path class="brand-mark__line" style="--i: 5" pathLength="1" d="M377 460v154M877 460v84" />
    </g>
</svg>
