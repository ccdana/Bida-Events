{{--
    Apertura de «El cambio de zapatos»: la caja clásica de la zapatería, de frente y en dos piezas. En
    el frente de laca, su nombre como la marca, el filete dorado y la etiqueta de modelo y talla; arriba,
    la tapa. Al tocarla, la tapa sube, se inclina hacia atrás sobre su canto y queda apoyada detrás de
    la caja, mostrando su forro. Después el papel de seda se abre hacia los dos costados por encima del
    borde y sus primeros tacones (stilettos de perfil, en espejo, punta con punta) suben un poco del
    papel, un brillo recorre el satén y aparece su nombre. Todo con la paleta del editor (la caja con el
    secundario, el satén con el principal, el papel con el acento). Solo transform y opacity. Lógica en
    shell/cover-component; estilos en css/invitation/tendencias/zapatos.css.
--}}
@php
    $introDate = \Illuminate\Support\Str::ucfirst($page->eventDate->locale(app()->getLocale())->translatedFormat(\App\Support\Localization::dateFormat('j \d\e F · H:i')));
@endphp

<div class="inv-themed-intro zp-intro"
    x-data="invitationCover({ part: 1250, reveal: 3150, close: 4000 })"
    x-show="!closed"
    :class="{ 'is-lifted': stage >= 1, 'is-open': stage >= 2 }"
    @click="open()"
    role="dialog"
    aria-modal="true"
    aria-label="{{ __('Invitación a los quince años de :displayName', ['displayName' => $page->displayName]) }}">
    <span class="zp-intro__light" aria-hidden="true"></span>
    <p class="zp-intro__eyebrow">{{ $invCopy['intro_eyebrow'] ?? __('Una caja muy especial') }}</p>

    <div class="zp-shop">
        {{-- El papel de seda, en dos mitades que se juntan al medio --}}
        <div class="zp-shop__inside" aria-hidden="true">
            <span class="zp-shop__tissue zp-shop__tissue--left"></span>
            <span class="zp-shop__tissue zp-shop__tissue--right"></span>
        </div>

        {{-- Adentro: los tacones, que suben del papel de seda y se paran sobre el borde --}}
        <div class="zp-shop__pair" aria-hidden="true">
            <span class="zp-shop__heel zp-shop__heel--left">
                @include('invitations.partials.tendencias.zapatos.heel', ['id' => 'zp-intro-left'])
            </span>
            <span class="zp-shop__heel zp-shop__heel--right">
                @include('invitations.partials.tendencias.zapatos.heel', ['id' => 'zp-intro-right'])
            </span>
            <span class="zp-shop__sparkles">
                @foreach([[18, 30], [82, 30], [50, 12], [30, 62], [70, 62]] as [$x, $y])
                    <i style="--x: {{ $x }}%; --y: {{ $y }}%; --n: {{ $loop->index }}"></i>
                @endforeach
            </span>
        </div>

        {{-- El frente de la caja, con su nombre como la marca --}}
        <div class="zp-shop__body" aria-hidden="true">
            <span class="zp-shop__frame"></span>
            <span class="zp-shop__made">{{ $invCopy['shoe_made'] ?? __('Hecho a mano para') }}</span>
            <span class="zp-shop__name">{{ $page->displayName }}</span>
            <span class="zp-shop__line">{{ $invCopy['shoe_line'] ?? __('Colección XV') }}</span>
            <span class="zp-shop__label">
                <span><small>{{ $invCopy['shoe_model'] ?? __('Modelo') }}</small>{{ $invCopy['shoe_model_name'] ?? __('Mis primeros tacones') }}</span>
                <span><small>{{ $invCopy['shoe_size'] ?? __('Talla') }}</small>XV</span>
            </span>
        </div>

        {{-- La tapa: el canto que se ve de frente y su forro, que queda a la vista cuando se apoya atrás --}}
        <div class="zp-shop__lid" aria-hidden="true">
            <span class="zp-shop__lid-inside"><i>{{ $invCopy['shoe_line'] ?? __('Colección XV') }}</i></span>
            <span class="zp-shop__lid-front"></span>
        </div>

        <button type="button" class="zp-shop__open" data-cover-trigger @click.stop="open()" aria-label="{{ __('Levantar la tapa y abrir la caja') }}"></button>
        <span class="zp-shop__shadow" aria-hidden="true"></span>
    </div>

    {{-- Su nombre, cuando aparecen los tacones --}}
    <p class="zp-intro__reveal" aria-hidden="true">
        <small>{{ $invCopy['shoe_made'] ?? __('Hecho a mano para') }}</small>
        {{ $page->displayName }}
    </p>

    <div class="zp-intro__meta">
        <p class="zp-intro__date">{{ $introDate }}</p>
        @if($guest)
            <p class="zp-intro__guest">{{ $invCopy['guest_banner_eyebrow'] ?? __('Hecho a medida para') }} {{ $guest->name }}</p>
        @endif
    </div>

    <p class="zp-intro__hint">{{ $invCopy['intro_hint'] ?? __('Toca la caja para abrirla') }}</p>
</div>
