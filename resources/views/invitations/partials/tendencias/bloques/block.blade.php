{{--
    Un bloque de juguete de «Bloques de juguete»: la cara del frente con su letra tallada y, en
    perspectiva, la cara de arriba (más clara) y la del costado (más oscura). El color sale de la
    paleta: 0 = principal, 1 = secundario, 2 = acento.
    Parámetros: letter, tone (0, 1 o 2), class y style (opcionales).
--}}
<span class="bl-block bl-tone--{{ $tone ?? 0 }} {{ $class ?? '' }}" @isset($style) style="{{ $style }}" @endisset>
    <span class="bl-block__face">{{ $letter ?? '' }}</span>
</span>
