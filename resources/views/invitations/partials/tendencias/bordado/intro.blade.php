{{--
    Apertura de «Bordado a mano»: sobre un mantel de cuadritos espera un bastidor con el lino tenso y,
    calcado en lápiz, el dibujo por bordar: su nombre y una guirnalda de florcitas. La aguja está
    clavada al principio del nombre. Al tocar, la aguja sube y baja recorriendo el dibujo y el nombre
    queda en hilo de satén; después se bordan las flores, una por una, y el bastidor se abre hasta la
    portada. Al lado, el carretel de hilo y unos botones sueltos.
    Lógica en shell/cover-component; estilos en css/invitation/tendencias/bordado.css.
--}}
@php
    $introDate = \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('j \d\e F \d\e Y'));
    // Florcitas del dibujo: x, y, tamaño, giro, con hojas (en el orden en que se bordan)
    $introFlowers = [
        [84, 76, 0.95, -18, true], [150, 50, 1.1, 0, true], [216, 76, 0.95, 18, true],
        [82, 232, 0.9, 200, true], [150, 256, 1.05, 180, true], [218, 232, 0.9, 160, true],
        [44, 150, 0.6, 90, false], [256, 150, 0.6, -90, false],
    ];
    // Nuditos sueltos entre las flores
    $introKnots = [[117, 60], [183, 60], [116, 244], [184, 244], [58, 112], [242, 112], [58, 190], [242, 190]];
@endphp

<div class="inv-themed-intro bd-intro"
    x-data="invitationCover({ part: 2750, reveal: 3250, close: 4050 })"
    x-show="!closed"
    :class="{ 'is-stitching': stage >= 1, 'is-open': stage >= 2 }"
    @click="open()"
    role="dialog"
    aria-modal="true"
    aria-label="Invitación al bautizo de {{ $page->displayName }}">
    <p class="bd-intro__eyebrow">
        @if($guest)
            {{ $invCopy['intro_eyebrow'] ?? 'Bordado con amor' }} para {{ $guest->name }}
        @else
            {{ $invCopy['intro_eyebrow'] ?? 'Bordado con amor' }}
        @endif
    </p>

    <button type="button" class="bd-hoop bd-intro__hoop" data-cover-trigger aria-label="Bordar su nombre y abrir la invitación">
        <span class="bd-hoop__clasp" aria-hidden="true"><i></i></span>
        <span class="bd-hoop__cloth">
            {{-- El dibujo calcado en lápiz y, encima, el hilo que lo va cubriendo --}}
            <svg class="bd-intro__garland" viewBox="0 0 300 300" aria-hidden="true" focusable="false">
                @foreach(['trace', 'thread'] as $layer)
                    <g class="bd-intro__{{ $layer }}">
                        @foreach($introFlowers as $index => [$x, $y, $size, $rotate, $leaves])
                            @include('invitations.partials.tendencias.bordado.flower', ['x' => $x, 'y' => $y, 'size' => $size, 'rotate' => $rotate, 'leaves' => $leaves, 'step' => $index])
                        @endforeach
                        @foreach($introKnots as $index => [$x, $y])
                            <circle class="bd-intro__knot" cx="{{ $x }}" cy="{{ $y }}" r="2.2" style="--i: {{ $index }}"/>
                        @endforeach
                    </g>
                @endforeach
            </svg>

            <span class="bd-intro__name">
                <span class="bd-intro__trace-name" aria-hidden="true">{{ $page->displayName }}</span>
                <span class="bd-satin bd-intro__satin">{{ $page->displayName }}</span>
                {{-- La aguja con su hilo: recorre el nombre subiendo y bajando --}}
                <span class="bd-needle" aria-hidden="true">
                    <svg viewBox="0 0 60 90" focusable="false">
                        <path class="bd-needle__thread" d="M38 10 C52 -4 58 22 44 30 C30 38 22 18 34 12"/>
                        <path class="bd-needle__steel" d="M36 6 C38.6 6 39.6 8 39.4 10.6 L32 78 L30.6 78 L33 10 C33.2 7.6 34 6 36 6 Z"/>
                        <ellipse class="bd-needle__eye" cx="36.2" cy="11.5" rx="1" ry="3" transform="rotate(6 36.2 11.5)"/>
                    </svg>
                </span>
            </span>
            <span class="bd-intro__date">{{ $introDate }}</span>
        </span>
    </button>

    <p class="bd-intro__hint">{{ $invCopy['intro_hint'] ?? 'Toca la aguja para bordar su nombre' }}</p>

    {{-- Sobre el mantel: el carretel del hilo del nombre y unos botones sueltos --}}
    <span class="bd-spool" aria-hidden="true"><i></i></span>
    @include('invitations.partials.tendencias.bordado.button', ['class' => 'bd-intro__button bd-intro__button--1'])
    @include('invitations.partials.tendencias.bordado.button', ['class' => 'bd-intro__button bd-intro__button--2'])
    @include('invitations.partials.tendencias.bordado.button', ['class' => 'bd-intro__button bd-intro__button--3'])
</div>
