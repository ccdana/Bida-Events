{{--
    Una prenda del tendedero: su forma de tela (enterito, medias o gorrito) con la costura del
    dobladillo y la pinza de madera que la sujeta al cordel. Si trae texto (el nombre en el enterito)
    va encima, con la letra de la invitación. Las formas son máscaras en tendencias/tendedero.css.
    Parámetros: kind (onesie, socks, hat), class, text (opcional) y fit (el texto se ajusta al pecho).
--}}
<span class="td-garment td-garment--{{ $kind }} {{ $class ?? '' }}">
    <span class="td-garment__cloth" aria-hidden="true"></span>
    <span class="td-garment__stitch" aria-hidden="true"></span>
    @if(!empty($text))
        {{-- Estampado en el pecho: con fit, el nombre se achica hasta entrar en la prenda --}}
        <span class="td-garment__chest">
            <span class="td-garment__print" @if(!empty($fit)) data-fit data-fit-max="44" data-fit-min="14" style="--fit-fallback: 1.4rem" @endif>{{ $text }}</span>
        </span>
    @endif
    <span class="td-pin" aria-hidden="true"></span>
    @if($kind === 'socks')
        <span class="td-pin td-pin--second" aria-hidden="true"></span>
    @endif
</span>
