{{--
    Apertura de «Carta de baile»: la carta cerrada sobre terciopelo, atada con un cordón que termina en
    un moño con su borla. Al tocarla, el moño se desata, las dos puntas del cordón se corren a los
    lados y la borla cae; la tapa se abre como un librito (se oscurece al girar y su sombra se va de
    la hoja) y adentro, en la lista de piezas, un lápiz escribe el nombre del invitado en la primera.
    Después la carta se acerca y deja ver la portada.
    Lógica en shell/cover-component; estilos en themes/carta.css.
--}}
@php
    $introDate = \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('j \d\e F'));
@endphp

<div class="inv-themed-intro cb-intro"
    x-data="invitationCover({ part: 950, reveal: 3000, close: 3750 })"
    x-show="!closed"
    :class="{ 'is-untied': stage >= 1, 'is-open': stage >= 2 }"
    role="dialog"
    aria-modal="true"
    aria-label="Invitación a los XV años de {{ $page->displayName }}">
    <p class="cb-intro__eyebrow">
        @if($guest)
            Para {{ $guest->name }}
        @else
            {{ $invCopy['intro_eyebrow'] ?? 'Tienes un lugar en el baile' }}
        @endif
    </p>

    <button type="button" class="cb-closed" data-cover-trigger @click="open()" aria-label="Desatar el cordón y abrir la carta">
        {{-- La primera hoja: la lista de piezas, con la primera reservada para el invitado --}}
        <span class="cb-closed__inside" aria-hidden="true">
            <span class="cb-inside__title">{{ $invCopy['card_title'] ?? 'Carta de baile' }}</span>
            <span class="cb-inside__rows">
                <span class="cb-inside__row is-reserved">
                    <span class="cb-inside__piece">Primera pieza</span>
                    @if($guest)
                        <span class="cb-inside__reserved">{{ $invCopy['card_reserved'] ?? 'Reservado para' }}</span>
                    @endif
                    <span class="cb-inside__line">
                        <span class="cb-inside__written">{{ $guest?->name ?? ($invCopy['card_reserved_any'] ?? 'Un lugar reservado para ti') }}</span>
                        {{-- El lápiz de la carta, que escribe el nombre --}}
                        <span class="cb-pencil">
                            <svg viewBox="0 0 24 120" focusable="false">
                                <path class="cb-pencil__wood" d="M5 18 H19 V100 L12 116 L5 100 Z"/>
                                <path class="cb-pencil__lead" d="M9.5 108 L12 116 L14.5 108 Z"/>
                                <rect class="cb-pencil__band" x="5" y="12" width="14" height="7" rx="1"/>
                                <rect class="cb-pencil__eraser" x="5" y="2" width="14" height="11" rx="3"/>
                            </svg>
                        </span>
                    </span>
                </span>
                <span class="cb-inside__row">
                    <span class="cb-inside__piece">Segunda pieza</span>
                    <span class="cb-inside__line"></span>
                </span>
                <span class="cb-inside__row">
                    <span class="cb-inside__piece">Tercera pieza</span>
                    <span class="cb-inside__line"></span>
                </span>
            </span>
            <span class="cb-inside__date">{{ $introDate }}</span>
        </span>

        <span class="cb-closed__cover">
            <span class="cb-closed__title">{{ $invCopy['card_title'] ?? 'Carta de baile' }}</span>
            <span class="cb-closed__name">{{ $page->displayName }}</span>
            <span class="cb-closed__date">{{ $introDate }}</span>
        </span>

        {{-- El cordón que abraza la carta: dos mitades que se corren al desatar el moño --}}
        <span class="cb-closed__cord" aria-hidden="true">
            <svg viewBox="0 0 220 120" preserveAspectRatio="none" focusable="false">
                <g class="cb-cord-half cb-cord-half--left">
                    <path class="cb-cord-line" d="M0 40 H110"/>
                    <path class="cb-cord-line cb-cord-line--under" d="M0 46 H110"/>
                </g>
                <g class="cb-cord-half cb-cord-half--right">
                    <path class="cb-cord-line" d="M110 40 H220"/>
                    <path class="cb-cord-line cb-cord-line--under" d="M110 46 H220"/>
                </g>
            </svg>
        </span>
        <span class="cb-closed__bow" aria-hidden="true">
            <svg viewBox="0 0 80 40" focusable="false">
                <path class="cb-bow__loop cb-bow__loop--left" d="M40 20 C30 4 6 4 7 19 C8 33 30 31 40 20 Z"/>
                <path class="cb-bow__loop cb-bow__loop--right" d="M40 20 C50 4 74 4 73 19 C72 33 50 31 40 20 Z"/>
                <ellipse class="cb-bow__knot" cx="40" cy="20" rx="6.5" ry="7.5"/>
            </svg>
        </span>
        <span class="cb-closed__tassel" aria-hidden="true">
            <svg viewBox="0 0 40 110" focusable="false">
                <path class="cb-tassel-string" d="M20 0 C14 16 26 26 20 44"/>
                <ellipse class="cb-tassel-knot" cx="20" cy="46" rx="6" ry="5"/>
                <path class="cb-tassel-cap" d="M13 50 H27 L25 58 H15 Z"/>
                <path class="cb-tassel-fringe" d="M15 58 L11 104 M18 58 L16 106 M20 58 L20 107 M22 58 L24 106 M25 58 L29 104"/>
            </svg>
        </span>
    </button>

    <p class="cb-intro__hint">{{ $invCopy['intro_hint'] ?? 'Toca la borla para desatar el cordón' }}</p>
</div>
