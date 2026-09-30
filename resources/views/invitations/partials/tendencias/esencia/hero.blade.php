{{--
    Portada de «Esencia XV»: el afiche de la campaña de lanzamiento. Arriba la línea de la fragancia y
    su nombre como marca; al centro la foto de la campaña con el frasco delante; debajo, el lanzamiento
    (día y hora), el lugar y el mensaje. Estilos en css/invitation/tendencias/esencia.css.
--}}
@php
    $heroMessage = $page->welcome['mensaje'] ?? '';
    $heroDay = ($page->welcome['fecha_texto'] ?? null)
        ?: \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('l j \d\e F'));
    $initial = mb_strtoupper(mb_substr(trim($page->displayName), 0, 1));
@endphp

<header id="inicio" class="inv-hero ez-hero">
    <p class="ez-kicker inv-fade-up">
        <span>{{ $invCopy['scent_line'] ?? 'Eau de Quince' }}</span>
        <span>{{ $invCopy['scent_edition'] ?? 'Edición' }} {{ $page->eventDate->format('Y') }}</span>
    </p>

    <h1 class="ez-brand inv-fade-up inv-fade-up--1">{{ $page->displayName }}</h1>

    <figure class="ez-campaign inv-fade-up inv-fade-up--1">
        <span class="ez-campaign__photo">
            @include('invitations.partials.tendencias.photo', ['widths' => [480, 768, 1200], 'width' => 768, 'sizes' => '(min-width: 640px) 20rem, 78vw'])
        </span>
        @include('invitations.partials.tendencias.esencia.bottle', ['id' => 'ez-hero-bottle', 'initial' => $initial, 'class' => 'ez-campaign__bottle'])
    </figure>

    <div class="ez-launch inv-fade-up inv-fade-up--2">
        <p class="ez-launch__label">{{ $invCopy['scent_launch'] ?? 'Lanzamiento' }}</p>
        <p class="ez-launch__when">{{ $heroDay }} · {{ $page->eventDate->format('H:i') }}</p>
        @if($page->placeName)
            <p class="ez-launch__place">{{ $page->placeName }}</p>
        @endif
    </div>

    @if(!empty($heroMessage))
        <p class="ez-message inv-fade-up inv-fade-up--3">{{ $heroMessage }}</p>
    @endif

    <a href="#contenido" class="inv-hero__scroll ez-hero__scroll">
        {{ $invCopy['scroll_hint'] ?? 'Desliza' }}
        <span class="inv-hero__scroll-line" aria-hidden="true"></span>
    </a>
</header>
