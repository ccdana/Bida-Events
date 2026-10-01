{{--
    Apertura de «Esencia XV»: la caja de lujo del perfume, en una toma de producto. Bajo el foco, sobre
    un pedestal de porcelana, la caja rígida de laca (vista de tres cuartos, en 3D: frente, costado y
    tapa) con su monograma, su nombre y el filete dorado; una cinta de satén la cruza por arriba y
    baja por el costado, atada con un moño. La caja flota apenas y un brillo recorre la laca. Al tocar
    el moño se desata, la cinta se desliza, la tapa sube y del interior sale luz; después la tapa se
    va, la caja baja y el frasco sube en su lugar, un destello cruza el cristal, el atomizador suelta su
    bruma y en ella aparece su nombre. Todo con la paleta del editor (la caja con el secundario, la
    cinta y la fragancia con el principal, el nácar con el acento). Solo transform y opacity. Lógica en
    shell/cover-component; estilos en css/invitation/tendencias/esencia.css.
--}}
@php
    $introDate = \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('j \d\e F · H:i'));
    $initial = mb_strtoupper(mb_substr(trim($page->displayName), 0, 1));
@endphp

<div class="inv-themed-intro ez-intro"
    x-data="invitationCover({ part: 1450, reveal: 2900, close: 3800 })"
    x-show="!closed"
    :class="{ 'is-untied': stage >= 1, 'is-open': stage >= 2 }"
    role="dialog"
    aria-modal="true"
    aria-label="Invitación al lanzamiento de {{ $page->displayName }}">
    <span class="ez-intro__spot" aria-hidden="true"></span>
    <p class="ez-intro__eyebrow">{{ $invCopy['intro_eyebrow'] ?? 'Lanzamiento exclusivo' }}</p>

    <div class="ez-stage">
        {{-- El pedestal de porcelana y la sombra de la caja --}}
        <span class="ez-plinth" aria-hidden="true"><i class="ez-plinth__side"></i><i class="ez-plinth__top"></i></span>
        <span class="ez-stage__shadow" aria-hidden="true"></span>

        {{-- El frasco que sube cuando se abre la caja, con su destello y la bruma del atomizador --}}
        <div class="ez-stage__bottle" aria-hidden="true">
            <span class="ez-mist">
                @foreach([[-1, 0.9], [-0.55, 1.2], [0, 1.35], [0.55, 1.2], [1, 0.9], [-0.3, 0.7], [0.3, 0.7]] as [$side, $reach])
                    <i style="--side: {{ $side }}; --reach: {{ $reach }}; --d: {{ $loop->index * 0.05 }}s"></i>
                @endforeach
            </span>
            @include('invitations.partials.tendencias.esencia.bottle', ['id' => 'ez-intro-bottle', 'initial' => $initial, 'class' => 'ez-stage__flacon'])
            <span class="ez-stage__glint"></span>
        </div>

        <div class="ez-float">
            {{-- La caja en 3D: el cuerpo (frente con la marca, costado con la cinta, arriba el papel de seda)
                 y la tapa (frente, costado con la cinta y arriba la cinta que la cruza) --}}
            <div class="ez-cube" aria-hidden="true">
                <div class="ez-cube__base">
                    <span class="ez-face ez-face--front">
                        <span class="ez-face__frame"></span>
                        <span class="ez-face__monogram">{{ $initial }}</span>
                        <span class="ez-face__brand">{{ $page->displayName }}</span>
                        <span class="ez-face__line">{{ $invCopy['scent_line'] ?? 'Eau de Quince' }}</span>
                    </span>
                    <span class="ez-face ez-face--side"><i class="ez-band ez-band--down"></i></span>
                    <span class="ez-face ez-face--top"><i class="ez-tissue"></i><i class="ez-glow"></i></span>
                </div>
                <div class="ez-cube__lid">
                    <span class="ez-face ez-face--front"></span>
                    <span class="ez-face ez-face--side"><i class="ez-band ez-band--down"></i></span>
                    <span class="ez-face ez-face--top"><i class="ez-band ez-band--across"></i></span>
                </div>
            </div>

            {{-- El moño de satén, sobre la tapa: se toca para desatarlo --}}
            <button type="button" class="ez-bow" data-cover-trigger @click="open()" aria-label="Desatar la cinta y abrir la caja">
                <svg class="ez-bow__svg" viewBox="0 0 120 84" aria-hidden="true" focusable="false">
                    <defs>
                        <linearGradient id="ez-satin-loop" x1="0" y1="0" x2="0" y2="1">
                            <stop class="ez-satin__light" offset="0"/>
                            <stop class="ez-satin__mid" offset="0.45"/>
                            <stop class="ez-satin__dark" offset="1"/>
                        </linearGradient>
                        <linearGradient id="ez-satin-tail" x1="0" y1="0" x2="1" y2="0">
                            <stop class="ez-satin__dark" offset="0"/>
                            <stop class="ez-satin__light" offset="0.5"/>
                            <stop class="ez-satin__dark" offset="1"/>
                        </linearGradient>
                    </defs>
                    <g class="ez-bow__tails">
                        <path d="M55 42 C50 54 43 64 34 79 L44 75 L47 84 C52 70 57 56 61 44 Z"/>
                        <path d="M65 42 C70 54 77 64 86 79 L76 75 L73 84 C68 70 63 56 59 44 Z"/>
                    </g>
                    <g class="ez-bow__loop ez-bow__loop--left">
                        <path d="M60 38 C48 15 22 6 12 16 C4 24 8 39 22 43 C36 47 50 43 60 38 Z"/>
                        <path class="ez-bow__fold" d="M58 37 C46 29 30 25 17 27"/>
                    </g>
                    <g class="ez-bow__loop ez-bow__loop--right">
                        <path d="M60 38 C72 15 98 6 108 16 C116 24 112 39 98 43 C84 47 70 43 60 38 Z"/>
                        <path class="ez-bow__fold" d="M62 37 C74 29 90 25 103 27"/>
                    </g>
                    <rect class="ez-bow__knot" x="52" y="29" width="16" height="17" rx="5"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Su nombre, que aparece en la bruma --}}
    <p class="ez-intro__reveal" aria-hidden="true">
        <small>{{ $invCopy['scent_line'] ?? 'Eau de Quince' }}</small>
        {{ $page->displayName }}
    </p>

    <div class="ez-intro__meta">
        <p class="ez-intro__date">{{ $introDate }}</p>
        @if($guest)
            <p class="ez-intro__guest">{{ $invCopy['guest_banner_eyebrow'] ?? 'Invitación para' }} {{ $guest->name }}</p>
        @endif
    </div>

    <p class="ez-intro__hint">{{ $invCopy['intro_hint'] ?? 'Toca la cinta para abrir la caja' }}</p>
</div>
