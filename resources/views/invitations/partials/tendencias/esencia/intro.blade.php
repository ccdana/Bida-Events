{{--
    Apertura de «Esencia XV»: la caja del perfume, negra, con su nombre como marca y la cinta cruzada
    con su moño. Mientras se espera, un brillo recorre la laca y el moño se mece. Al tocar el moño se
    desata: la cinta se desliza y cae, la tapa sube, el frasco se levanta de la caja y el atomizador
    suelta su bruma, en la que aparece su nombre. Después se pasa a la portada. Solo se anima
    transform y opacity. Lógica en shell/cover-component; estilos en css/invitation/tendencias/esencia.css.
--}}
@php
    $introDate = \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('j \d\e F · H:i'));
    $initial = mb_strtoupper(mb_substr(trim($page->displayName), 0, 1));
@endphp

<div class="inv-themed-intro ez-intro"
    x-data="invitationCover({ part: 1400, reveal: 2800, close: 3700 })"
    x-show="!closed"
    :class="{ 'is-untied': stage >= 1, 'is-open': stage >= 2 }"
    role="dialog"
    aria-modal="true"
    aria-label="Invitación al lanzamiento de {{ $page->displayName }}">
    <p class="ez-intro__eyebrow">{{ $invCopy['intro_eyebrow'] ?? 'Lanzamiento exclusivo' }}</p>

    <div class="ez-box">
        {{-- El frasco, adentro de la caja, con la bruma del atomizador --}}
        <div class="ez-box__bottle">
            <span class="ez-mist" aria-hidden="true">
                @foreach([[-1, 0.9], [-0.55, 1.2], [0, 1.35], [0.55, 1.2], [1, 0.9], [-0.3, 0.7], [0.3, 0.7]] as [$side, $reach])
                    <i style="--side: {{ $side }}; --reach: {{ $reach }}; --d: {{ $loop->index * 0.05 }}s"></i>
                @endforeach
            </span>
            @include('invitations.partials.tendencias.esencia.bottle', ['id' => 'ez-intro-bottle', 'initial' => $initial])
        </div>

        <div class="ez-box__base" aria-hidden="true"></div>
        <div class="ez-box__lid">
            <span class="ez-box__brand">{{ $page->displayName }}</span>
            <span class="ez-box__line">{{ $invCopy['scent_line'] ?? 'Eau de Quince' }}</span>
        </div>

        {{-- La cinta cruzada y el moño: se toca para desatarlo --}}
        <span class="ez-ribbon ez-ribbon--v" aria-hidden="true"></span>
        <span class="ez-ribbon ez-ribbon--h" aria-hidden="true"></span>
        <button type="button" class="ez-bow" data-cover-trigger @click="open()" aria-label="Desatar la cinta y abrir la caja">
            <svg class="ez-bow__svg" viewBox="0 0 120 70" aria-hidden="true" focusable="false">
                <path class="ez-bow__loop" d="M60 32 C44 6 12 4 10 22 C8 40 40 40 60 32 Z"/>
                <path class="ez-bow__loop" d="M60 32 C76 6 108 4 110 22 C112 40 80 40 60 32 Z"/>
                <path class="ez-bow__tail" d="M56 36 L40 68 L48 64 L52 70 L60 38 Z"/>
                <path class="ez-bow__tail" d="M64 36 L80 68 L72 64 L68 70 L60 38 Z"/>
                <rect class="ez-bow__knot" x="52" y="25" width="16" height="15" rx="4"/>
            </svg>
        </button>
    </div>

    {{-- Su nombre, que aparece en la bruma --}}
    <p class="ez-intro__reveal" aria-hidden="true">{{ $page->displayName }}</p>

    <div class="ez-intro__meta">
        <p class="ez-intro__date">{{ $introDate }}</p>
        @if($guest)
            <p class="ez-intro__guest">{{ $invCopy['guest_banner_eyebrow'] ?? 'Invitación para' }} {{ $guest->name }}</p>
        @endif
    </div>

    <p class="ez-intro__hint">{{ $invCopy['intro_hint'] ?? 'Toca la cinta para abrir la caja' }}</p>
</div>
