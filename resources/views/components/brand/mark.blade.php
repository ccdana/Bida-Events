{{--
    Isotipo de Bida Events: una "b" minúscula dibujada como un arco de entrada
    (el umbral de un salón, una capilla o un jardín) con un punto de luz en el
    centro del arco, que representa el momento que se celebra.
    La forma usa currentColor y el punto el color de acento de la marca.
--}}
@props(['animated' => false])

<svg viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"
    {{ $attributes->class(['brand-mark', 'brand-mark--animated' => $animated]) }}>
    <path class="brand-mark__shape" fill="currentColor" fill-rule="evenodd"
        d="M6 29V5.5a2.5 2.5 0 0 1 5 0v4.06A10.5 10.5 0 0 1 27 18.5V29H6Zm5-5h11v-5.5a5.5 5.5 0 0 0-11 0V24Z" />
    <circle class="brand-mark__dot" cx="16.5" cy="18.5" r="2.25" />
</svg>
