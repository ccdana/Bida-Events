{{--
    Apertura de «Joyero musical»: el joyero cerrado, de madera lacada con su filete de marquetería, las
    patas de metal, la cerradura y la llave puesta; en la tapa, la placa esmaltada con su nombre.
    Mientras se espera, la llave se mece y un brillo recorre la laca. Al tocar la llave gira media
    vuelta, la tapa se levanta y se despliega su forro de terciopelo capitoné, y sube la figura de la
    quinceañera girando sobre su pie, como en los joyeros de música (el toque también arranca la
    canción, si la invitación tiene una). Después se entra en el joyero hasta la portada. Solo se anima
    transform y opacity. Lógica en shell/cover-component; estilos en css/invitation/tendencias/joyero.css.
--}}
@php
    $introDate = \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('j \d\e F · H:i'));
@endphp

<div class="inv-themed-intro jo-intro"
    x-data="invitationCover({ part: 1800, reveal: 3100, close: 4000 })"
    x-show="!closed"
    :class="{ 'is-unlocked': stage >= 1, 'is-open': stage >= 2 }"
    role="dialog"
    aria-modal="true"
    aria-label="Invitación a los XV de {{ $page->displayName }}">
    <p class="jo-intro__eyebrow">{{ $invCopy['intro_eyebrow'] ?? 'Un recuerdo para siempre' }}</p>

    <div class="jo-box">
        {{-- El forro de la tapa: se despliega hacia arriba al abrir --}}
        <div class="jo-box__lining" aria-hidden="true">
            <span class="jo-box__lining-name">{{ $page->displayName }}</span>
        </div>

        {{-- La figura de la quinceañera sobre su pie, que sube y gira --}}
        <div class="jo-figure" aria-hidden="true">
            <svg class="jo-figure__svg" viewBox="0 0 60 120" focusable="false">
                <defs>
                    <linearGradient id="jo-brass" x1="0" y1="0" x2="1" y2="1">
                        <stop class="jo-brass__hi" offset="0"/>
                        <stop class="jo-brass__mid" offset="0.5"/>
                        <stop class="jo-brass__low" offset="1"/>
                    </linearGradient>
                </defs>
                <g fill="url(#jo-brass)">
                    <circle cx="30" cy="16" r="6"/>
                    <circle cx="30" cy="8.5" r="3.6"/>
                    <path d="M27.5 21 L32.5 21 L33 25 C38 27 38 34 36 42 L24 42 C22 34 22 27 27 25 Z"/>
                    <path d="M25 27 C19 24 16 17 18 10 C18.5 8.5 20.5 8.8 20.3 10.5 C19 16 21.5 21 26 24 Z"/>
                    <path d="M35 27 C41 28 45 32 47 37 C47.6 38.6 45.8 39.4 45 38 C43 34 39 31 34.5 30 Z"/>
                    <path d="M24 41 C16 56 9 80 5 104 C18 110 42 110 55 104 C51 80 44 56 36 41 Z"/>
                    <ellipse cx="30" cy="111" rx="16" ry="4"/>
                    <rect x="28.5" y="111" width="3" height="8"/>
                </g>
                <path class="jo-figure__fold" d="M27 46 C24 64 21 84 19 106 M33 46 C36 64 39 84 41 106 M30 44 V108"/>
            </svg>
        </div>

        {{-- La tapa cerrada, con la placa esmaltada --}}
        <div class="jo-box__lid" aria-hidden="true">
            <span class="jo-box__plaque">
                <span class="jo-box__plaque-name">{{ $page->displayName }}</span>
                <span class="jo-box__plaque-xv">XV</span>
            </span>
        </div>

        {{-- El cuerpo: la marquetería, la cerradura y la llave --}}
        <div class="jo-box__body">
            <span class="jo-box__inlay" aria-hidden="true"></span>
            <button type="button" class="jo-key" data-cover-trigger @click="open()" aria-label="Girar la llave y abrir el joyero">
                <span class="jo-key__plate" aria-hidden="true"></span>
                <svg class="jo-key__svg" viewBox="0 0 40 80" aria-hidden="true" focusable="false">
                    <g fill="url(#jo-brass)">
                        <path d="M20 2 C28 2 33 8 33 14 C33 20 28 25 22 26 L22 30 L18 30 L18 26 C12 25 7 20 7 14 C7 8 12 2 20 2 Z M20 7 C15.5 7 12 10 12 14 C12 18 15.5 21 20 21 C24.5 21 28 18 28 14 C28 10 24.5 7 20 7 Z"/>
                        <rect x="18" y="30" width="4" height="40"/>
                        <rect x="22" y="58" width="7" height="3.5"/>
                        <rect x="22" y="64" width="5" height="3.5"/>
                    </g>
                </svg>
            </button>
        </div>
        <span class="jo-box__feet" aria-hidden="true"></span>
    </div>

    <div class="jo-intro__meta">
        <p class="jo-intro__date">{{ $introDate }}</p>
        @if($guest)
            <p class="jo-intro__guest">{{ $invCopy['guest_banner_eyebrow'] ?? 'Invitación para' }} {{ $guest->name }}</p>
        @endif
    </div>

    <p class="jo-intro__hint">{{ $invCopy['intro_hint'] ?? 'Toca la llave para abrir el joyero' }}</p>
</div>
