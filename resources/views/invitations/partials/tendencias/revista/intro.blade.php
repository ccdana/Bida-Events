{{--
    Apertura de «Edición especial»: el kiosco. Bajo el toldo, en el exhibidor, entre otras revistas,
    está la edición de colección todavía envuelta en su faja de papel («Edición de colección ·
    Promoción 2026»); en el borde del estante, una nota con el nombre del invitado. Al tocarla, la
    revista se saca del exhibidor, la faja se rompe en dos y cae, la tapa se abre y la invitación
    aparece. Lógica en shell/cover-component; estilos en tendencias/revista.css.
--}}
@php
    $introYear = $page->eventDate->format('Y');
@endphp

<div class="inv-themed-intro rv-intro"
    x-data="invitationCover({ part: 1000, reveal: 1700, close: 2600 })"
    x-show="!closed"
    :class="{ 'is-torn': stage >= 1, 'is-open': stage >= 2 }"
    role="dialog"
    aria-modal="true"
    aria-label="Invitación a la graduación de {{ $page->displayName }}">
    <span class="rv-awning" aria-hidden="true"></span>

    <div class="rv-rack">
        {{-- Otras revistas del exhibidor, detrás --}}
        <span class="rv-rack__issue rv-rack__issue--left" aria-hidden="true"><i></i></span>
        <span class="rv-rack__issue rv-rack__issue--right" aria-hidden="true"><i></i></span>

        <button type="button" class="rv-issue" data-cover-trigger @click="open()" aria-label="Romper la faja y abrir la revista">
            {{-- Las hojas de adentro: se ven cuando se abre la tapa --}}
            <span class="rv-issue__inside" aria-hidden="true">
                <span class="rv-issue__inside-title">{{ $invCopy['mag_contents'] ?? 'En esta edición' }}</span>
                <span class="rv-issue__inside-lines"></span>
            </span>

            <span class="rv-issue__cover">
                <span class="rv-issue__masthead">{{ $invCopy['mag_name'] ?? 'Promoción' }}</span>
                <span @class(['rv-issue__photo', 'is-typographic' => ! $page->heroImage])>
                    @if($page->heroImage)
                        @include('invitations.partials.tendencias.photo', ['widths' => [360, 720], 'width' => 720, 'sizes' => '16rem'])
                    @else
                        <span class="rv-cover__type" aria-hidden="true"><span>{{ substr($introYear, 0, 2) }}</span><span>{{ substr($introYear, 2) }}</span></span>
                    @endif
                </span>
                <span class="rv-issue__name">{{ $page->displayName }}</span>
            </span>

            {{-- La faja: dos mitades que se separan al romperse --}}
            <span class="rv-band" aria-hidden="true">
                <span class="rv-band__half rv-band__half--left"><span>{{ $invCopy['intro_eyebrow'] ?? 'Edición de colección' }}</span></span>
                <span class="rv-band__half rv-band__half--right"><span>{{ $invCopy['mag_name'] ?? 'Promoción' }} {{ $introYear }}</span></span>
            </span>
        </button>

        <span class="rv-rack__shelf" aria-hidden="true"></span>

        @if($guest)
            {{-- La nota pegada al estante: el ejemplar está apartado --}}
            <p class="rv-intro__guest">{{ $invCopy['guest_banner_eyebrow'] ?? 'Ejemplar reservado para' }} <b>{{ $guest->name }}</b></p>
        @endif
    </div>

    <p class="rv-intro__hint">{{ $invCopy['intro_hint'] ?? 'Toca para romper la faja' }}</p>
</div>
