{{--
    Apertura de «El cambio de zapatos»: la caja de la zapatería, vista desde arriba sobre la mesa de la
    tienda. Es una caja de cajón: la funda de laca con su nombre como marca, el filete dorado y la
    etiqueta de modelo y talla, y abajo asoma la cinta de satén para tirar del cajón. Al tocarla, la
    cinta se estira, la funda se desliza y deja el cajón a la vista: el papel de seda cerrado con el
    sello de su inicial. Después el sello salta, el papel se abre hacia los dos lados y aparecen sus
    primeros tacones (uno con la punta hacia arriba y el otro hacia abajo, como vienen en la caja) con
    su nombre grabado en la plantilla; un brillo recorre el satén y destella. Todo con la paleta del
    editor (la caja con el secundario, el satén con el principal, el papel con el acento). Solo
    transform y opacity. Lógica en shell/cover-component; estilos en css/invitation/tendencias/zapatos.css.
--}}
@php
    $introDate = \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('j \d\e F · H:i'));
    $firstName = \Illuminate\Support\Str::of($page->displayName)->explode(' ')->first();
    $initial = mb_strtoupper(mb_substr(trim($page->displayName), 0, 1));
@endphp

<div class="inv-themed-intro zp-intro"
    x-data="invitationCover({ part: 1250, reveal: 2850, close: 3750 })"
    x-show="!closed"
    :class="{ 'is-pulled': stage >= 1, 'is-open': stage >= 2 }"
    @click="open()"
    role="dialog"
    aria-modal="true"
    aria-label="Invitación a los quince años de {{ $page->displayName }}">
    <span class="zp-intro__light" aria-hidden="true"></span>
    <p class="zp-intro__eyebrow">{{ $invCopy['intro_eyebrow'] ?? 'Una caja muy especial' }}</p>

    <div class="zp-box">
        {{-- El cajón: el borde de la caja, el fondo y los tacones; encima, el papel de seda con su sello --}}
        <div class="zp-tray" aria-hidden="true">
            <span class="zp-tray__floor"></span>
            <div class="zp-tray__pair">
                @include('invitations.partials.tendencias.zapatos.shoe', ['id' => 'zp-intro-left', 'name' => $firstName, 'class' => 'zp-tray__shoe zp-tray__shoe--left'])
                @include('invitations.partials.tendencias.zapatos.shoe', ['id' => 'zp-intro-right', 'class' => 'zp-tray__shoe zp-tray__shoe--right'])
            </div>
            <span class="zp-tray__sparkles">
                @foreach([[30, 22], [70, 30], [24, 62], [76, 70], [50, 48]] as [$x, $y])
                    <i style="--x: {{ $x }}%; --y: {{ $y }}%; --n: {{ $loop->index }}"></i>
                @endforeach
            </span>
            <span class="zp-tissue zp-tissue--left"></span>
            <span class="zp-tissue zp-tissue--right"></span>
            <span class="zp-seal">{{ $initial }}</span>
        </div>

        {{-- La funda de laca, con su nombre como marca --}}
        <div class="zp-sleeve" aria-hidden="true">
            <span class="zp-sleeve__frame"></span>
            <span class="zp-sleeve__made">{{ $invCopy['shoe_made'] ?? 'Hecho a mano para' }}</span>
            <span class="zp-sleeve__name">{{ $page->displayName }}</span>
            <span class="zp-sleeve__line">{{ $invCopy['shoe_line'] ?? 'Colección XV' }}</span>
            <span class="zp-sleeve__label">
                <span><small>{{ $invCopy['shoe_model'] ?? 'Modelo' }}</small>{{ $invCopy['shoe_model_name'] ?? 'Mis primeros tacones' }}</span>
                <span><small>{{ $invCopy['shoe_size'] ?? 'Talla' }}</small>XV</span>
            </span>
        </div>

        {{-- La cinta para tirar del cajón --}}
        <button type="button" class="zp-pull" data-cover-trigger @click="open()" aria-label="Tirar de la cinta y abrir la caja">
            <span class="zp-pull__ribbon" aria-hidden="true"></span>
        </button>
    </div>

    {{-- Su nombre, cuando aparecen los tacones --}}
    <p class="zp-intro__reveal" aria-hidden="true">
        <small>{{ $invCopy['shoe_made'] ?? 'Hecho a mano para' }}</small>
        {{ $page->displayName }}
    </p>

    <div class="zp-intro__meta">
        <p class="zp-intro__date">{{ $introDate }}</p>
        @if($guest)
            <p class="zp-intro__guest">{{ $invCopy['guest_banner_eyebrow'] ?? 'Hecho a medida para' }} {{ $guest->name }}</p>
        @endif
    </div>

    <p class="zp-intro__hint">{{ $invCopy['intro_hint'] ?? 'Toca la caja para abrirla' }}</p>
</div>
