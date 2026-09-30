{{--
    Apertura de «Mesa de honor»: su lugar en la mesa del banquete, visto desde arriba sobre el mantel de
    lino. Arriba la tarjeta con los nombres de los novios y a quién está reservado el lugar; en medio el
    plato con su filete de oro y, encima, la servilleta doblada con su servilletero; a los lados el
    tenedor, el cuchillo y la cuchara, y dos copas arriba. Dos velas tiemblan a los costados. Al tocar el
    servilletero se desliza fuera, la servilleta se levanta y se aparta, y en el plato aparece el
    monograma de los novios; después se acerca el plato hasta la portada. Solo se anima transform y
    opacity. Lógica en shell/cover-component; estilos en css/invitation/tendencias/mesa.css.
--}}
@php
    $introDate = \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('j \d\e F · H:i'));
    $names = $page->names();
    $monogram = collect($names)->map(fn (string $name) => mb_strtoupper(mb_substr($name, 0, 1)))->take(2)->implode(' & ');
@endphp

<div class="inv-themed-intro ms-intro"
    x-data="invitationCover({ part: 1300, reveal: 2700, close: 3600 })"
    x-show="!closed"
    :class="{ 'is-unfolded': stage >= 1, 'is-open': stage >= 2 }"
    role="dialog"
    aria-modal="true"
    aria-label="Invitación a la boda de {{ $page->displayName }}">
    <span class="ms-intro__candle ms-intro__candle--left" aria-hidden="true"></span>
    <span class="ms-intro__candle ms-intro__candle--right" aria-hidden="true"></span>

    <p class="ms-intro__eyebrow">{{ $invCopy['intro_eyebrow'] ?? 'Tienes un lugar en nuestra mesa' }}</p>

    <div class="ms-setting">
        {{-- La tarjeta del lugar, doblada en carpa --}}
        <div class="ms-placecard">
            <span class="ms-placecard__names">{{ $page->displayName }}</span>
            <span class="ms-placecard__guest">
                @if($guest)
                    {{ $invCopy['guest_banner_eyebrow'] ?? 'Mesa reservada para' }} {{ $guest->name }}
                @else
                    {{ $invCopy['table_seat'] ?? 'Su lugar en nuestra mesa' }}
                @endif
            </span>
        </div>

        <span class="ms-glass ms-glass--left" aria-hidden="true"></span>
        <span class="ms-glass ms-glass--right" aria-hidden="true"></span>

        <div class="ms-setting__row">
            @include('invitations.partials.tendencias.mesa.cutlery', ['piece' => 'fork', 'class' => 'ms-setting__fork'])

            <div class="ms-plate">
                <span class="ms-plate__well" aria-hidden="true">
                    <span class="ms-plate__monogram">{{ $monogram }}</span>
                    <span class="ms-plate__names">{{ $page->displayName }}</span>
                </span>

                {{-- La servilleta doblada sobre el plato, con su servilletero: se toca para abrir --}}
                <button type="button" class="ms-napkin" data-cover-trigger @click="open()" aria-label="Quitar el servilletero y abrir la servilleta">
                    <span class="ms-napkin__cloth" aria-hidden="true"></span>
                    <span class="ms-napkin__ring" aria-hidden="true"></span>
                </button>
            </div>

            <span class="ms-setting__right" aria-hidden="true">
                @include('invitations.partials.tendencias.mesa.cutlery', ['piece' => 'knife'])
                @include('invitations.partials.tendencias.mesa.cutlery', ['piece' => 'spoon'])
            </span>
        </div>
    </div>

    <p class="ms-intro__date">{{ $introDate }}</p>
    <p class="ms-intro__hint">{{ $invCopy['intro_hint'] ?? 'Toca el servilletero para abrir' }}</p>
</div>
