{{--
    Apertura de «Cabina de fotos»: la cabina de la fiesta vista de frente, con su letrero encendido
    arriba, la cortina de terciopelo corrida, la pantallita, el botón grande y la ranura por donde sale
    la tira. Al tocar el botón la pantallita cuenta 3, 2, 1, salta el flash detrás de la cortina en cada
    una de las tres poses (despacio: nunca más de tres destellos por segundo) y la tira baja por la
    ranura revelándose, con el nombre y el día al pie. Después se acerca la tira hasta la portada. Solo
    se anima transform y opacity. Lógica en shell/cover-component; estilos en
    css/invitation/tendencias/cabina.css.
--}}
<div class="inv-themed-intro cb-intro"
    x-data="invitationCover({ part: 3200, reveal: 3800, close: 4600 })"
    x-show="!closed"
    :class="{ 'is-shooting': stage >= 1, 'is-open': stage >= 2 }"
    role="dialog"
    aria-modal="true"
    aria-label="Invitación al cumpleaños de {{ $page->displayName }}">
    <span class="cb-intro__spill" aria-hidden="true"></span>

    <p class="cb-intro__eyebrow">{{ $invCopy['intro_eyebrow'] ?? 'Pasa, que la foto te espera' }}</p>
    @if($guest)
        <p class="cb-intro__guest">{{ $invCopy['guest_banner_eyebrow'] ?? 'Esta tira es para' }} <strong>{{ $guest->name }}</strong></p>
    @endif

    <div class="cb-scene">
        <div class="cb-booth">
            <p class="cb-booth__sign" aria-hidden="true">{{ $invCopy['booth_sign'] ?? 'Fotos' }}</p>

            <div class="cb-booth__body">
                {{-- La cortina corrida: los flashes se ven por la abertura del medio --}}
                <span class="cb-booth__window" aria-hidden="true">
                    <span class="cb-booth__flash"></span>
                    <span class="cb-curtain cb-curtain--left"></span>
                    <span class="cb-curtain cb-curtain--right"></span>
                    <span class="cb-booth__glare"></span>
                    <span class="cb-booth__rod"></span>
                </span>

                <span class="cb-booth__screen" aria-hidden="true">
                    <span class="cb-booth__ready">{{ $invCopy['booth_ready'] ?? 'Mira a la cámara' }}</span>
                    <i style="--n: 0">3</i>
                    <i style="--n: 1">2</i>
                    <i style="--n: 2">1</i>
                </span>

                <button type="button" class="cb-booth__button" data-cover-trigger @click="open()" aria-label="Apretar el botón para tomar las fotos">
                    <span>{{ $invCopy['booth_button'] ?? '¡Foto!' }}</span>
                </button>

                <span class="cb-booth__slot" aria-hidden="true">
                    <small>{{ $invCopy['booth_slot'] ?? 'Tus fotos salen aquí' }}</small>
                </span>
            </div>
        </div>

        {{-- La tira sale por la ranura, empezando por el pie con el nombre --}}
        <div class="cb-drop" aria-hidden="true">
            @include('invitations.partials.tendencias.cabina.strip', ['class' => 'cb-strip--intro'])
        </div>
    </div>

    <p class="cb-intro__hint">{{ $invCopy['intro_hint'] ?? 'Toca el botón para la foto' }}</p>
</div>
