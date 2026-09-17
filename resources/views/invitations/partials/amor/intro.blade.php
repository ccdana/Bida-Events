{{--
    Apertura de la carta de amor: una hoja doblada en tres, atada con una cinta de raso y un moño.
    Al tocar, el moño se suelta y la cinta cae (etapa 1), la hoja se despliega de arriba y de abajo
    mostrando el «Para…» escrito a mano (etapa 2), y la carta crece y se desvanece hacia la portada.
    Lógica en shell/cover-component, estilos en resources/css/cards/amor.css.
--}}
@php
    $introTo = $cardTo !== '' ? $cardTo : null;
    $introFrom = $cardFrom !== '' ? $cardFrom : null;
@endphp

<div class="inv-amor-intro"
    x-data="invitationCover({ part: 650, reveal: 1500, close: 2300 })"
    x-show="!closed"
    :class="{ 'is-untying': stage >= 1, 'is-unfolding': stage >= 2 }"
    @click="open()"
    role="dialog"
    aria-modal="true"
    aria-label="{{ $introTo ? 'Carta para '.$introTo : 'Una carta para ti' }}">
    <div class="inv-amor-intro__content">
        <p class="inv-amor-intro__eyebrow">{{ $introFrom ? 'Tienes una carta de '.$introFrom : 'Tienes una carta' }}</p>

        <button type="button" class="inv-amor-letter" data-cover-trigger aria-label="Abrir la carta">
            {{-- Pliegue de arriba: al abrir sale girando desde el borde superior --}}
            <span class="inv-amor-letter__fold inv-amor-letter__fold--top" aria-hidden="true"></span>

            {{-- Panel central: lo que se lee al desplegar --}}
            <span class="inv-amor-letter__sheet" aria-hidden="true">
                <span class="inv-amor-letter__greeting">{{ $introTo ? $introTo.',' : 'Hola,' }}</span>
                <span class="inv-amor-letter__lines"><i></i><i></i><i></i></span>

                {{-- Dorso de la carta doblada, con el destinatario: se retira al desplegarla --}}
                <span class="inv-amor-letter__cover">{{ $introTo ? 'Para '.$introTo : 'Para ti' }}</span>
            </span>

            <span class="inv-amor-letter__fold inv-amor-letter__fold--bottom" aria-hidden="true"></span>

            {{-- Cinta y moño --}}
            <span class="inv-amor-letter__ribbon" aria-hidden="true"></span>
            <svg class="inv-amor-letter__bow" viewBox="0 0 120 70" aria-hidden="true">
                <path class="inv-amor-letter__bow-loop" d="M60 35 C 42 8, 8 6, 10 30 C 12 52, 40 50, 60 35 Z" />
                <path class="inv-amor-letter__bow-loop" d="M60 35 C 78 8, 112 6, 110 30 C 108 52, 80 50, 60 35 Z" />
                <path class="inv-amor-letter__bow-tail" d="M56 38 C 50 50, 44 60, 38 68 L 48 68 C 52 60, 56 50, 60 42 Z" />
                <path class="inv-amor-letter__bow-tail" d="M64 38 C 70 50, 76 60, 82 68 L 72 68 C 68 60, 64 50, 60 42 Z" />
                <ellipse class="inv-amor-letter__bow-knot" cx="60" cy="36" rx="8" ry="9" />
            </svg>
        </button>

        <p class="inv-amor-intro__hint">
            <span class="inv-amor-intro__hint-dot" aria-hidden="true"></span>
            {{ $invCopy['intro_hint'] ?? 'Toca la cinta para abrir la carta' }}
        </p>
    </div>
</div>
