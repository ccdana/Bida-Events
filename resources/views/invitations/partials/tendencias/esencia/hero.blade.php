{{--
    Portada de «Esencia XV»: el afiche de la campaña de lanzamiento. Arriba la línea de la fragancia y
    su nombre como marca; al centro la foto de la campaña con el frasco delante; debajo, el lanzamiento
    (día y hora), el lugar y el mensaje. Al abrirse la caja el nombre aparece desde la bruma, la foto
    se asienta, el frasco sube, rocía y queda flotando; al hacer scroll el frasco va por delante de la
    foto (data-parallax) y al tocarlo vuelve a rociar (data-poke). Estilos en
    css/invitation/tendencias/esencia.css.
--}}
@php
    $heroMessage = $page->welcome['mensaje'] ?? '';
    $heroDay = ($page->welcome['fecha_texto'] ?? null)
        ?: \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('l j \d\e F'));
    $initial = mb_strtoupper(mb_substr(trim($page->displayName), 0, 1));
@endphp

<header id="inicio" class="inv-hero ez-hero">
    <p class="ez-kicker">
        <span>{{ $invCopy['scent_line'] ?? 'Eau de Quince' }}</span>
        <span>{{ $invCopy['scent_edition'] ?? 'Edición' }} {{ $page->eventDate->format('Y') }}</span>
    </p>

    <h1 class="ez-brand">{{ $page->displayName }}</h1>

    <figure class="ez-campaign">
        <span class="ez-campaign__photo">
            @include('invitations.partials.tendencias.photo', ['widths' => [480, 768, 1200], 'width' => 768, 'sizes' => '(min-width: 640px) 20rem, 78vw'])
        </span>
        <span class="ez-campaign__bottle" data-poke="spritz" data-parallax="0.07">
            @include('invitations.partials.tendencias.esencia.bottle', ['id' => 'ez-hero-bottle', 'initial' => $initial, 'class' => 'ez-campaign__svg'])
            {{-- La bruma del atomizador: sale sola cuando el frasco termina de subir y cada vez que se toca --}}
            <span class="ez-spritz" aria-hidden="true">
                <i data-poke-puff></i><i data-poke-puff></i><i data-poke-puff></i>
            </span>
        </span>
    </figure>

    <div class="ez-launch">
        <p class="ez-launch__label">{{ $invCopy['scent_launch'] ?? 'Lanzamiento' }}</p>
        <p class="ez-launch__when">{{ $heroDay }} · {{ $page->eventDate->format('H:i') }}</p>
        @if($page->placeName)
            <p class="ez-launch__place">{{ $page->placeName }}</p>
        @endif
    </div>

    @if(!empty($heroMessage))
        <p class="ez-message">{{ $heroMessage }}</p>
    @endif

    <a href="#contenido" class="inv-hero__scroll ez-hero__scroll">
        {{ $invCopy['scroll_hint'] ?? 'Desliza' }}
        <span class="inv-hero__scroll-line" aria-hidden="true"></span>
    </a>
</header>
