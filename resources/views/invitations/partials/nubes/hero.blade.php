{{--
    Portada de «Entre nubes»: la foto es el sol entre las nubes. Un círculo con su aro y rayos finos
    que giran despacio; delante, dos nubes que tapan el borde de abajo, y arriba nubes lejanas que
    pasan. Debajo, el nombre, la fecha en una nubecita y el mensaje; al pie, el banco de nubes por
    donde se entra al resto de la invitación. Estilos en themes/nubes.css.
--}}
@php
    $heroEyebrow = ($page->welcome['subtitulo'] ?? null) ?: ($invCopy['hero_eyebrow'] ?? 'Mi bautizo');
    $heroMessage = $page->welcome['mensaje'] ?? '';
    $heroDay = ($page->welcome['fecha_texto'] ?? null)
        ?: \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('l j \d\e F'));
@endphp

<header id="inicio" class="inv-hero nb-hero">
    <div class="nb-hero__far" aria-hidden="true">
        @foreach([1, 2, 3] as $far)
            @include('invitations.partials.nubes.cloud', ['class' => "nb-hero__far-cloud nb-hero__far-cloud--{$far}"])
        @endforeach
    </div>

    <p class="nb-kicker inv-fade-up">{{ $heroEyebrow }}</p>

    <div class="nb-sun inv-fade-up inv-fade-up--1">
        <span class="nb-sun__rays" aria-hidden="true"></span>
        <span class="nb-sun__ring" aria-hidden="true"></span>
        <div class="nb-sun__photo">
            @include('invitations.partials.tendencias.photo', ['widths' => [480, 768], 'width' => 768, 'sizes' => '(min-width: 640px) 18rem, 66vw', 'parallax' => '0.06'])
        </div>
        @include('invitations.partials.nubes.cloud', ['class' => 'nb-sun__cloud nb-sun__cloud--left', 'shade' => true])
        @include('invitations.partials.nubes.cloud', ['class' => 'nb-sun__cloud nb-sun__cloud--right', 'shade' => true])
    </div>

    <h1 class="nb-name inv-fade-up inv-fade-up--2">{{ $page->displayName }}</h1>

    <p class="nb-when inv-fade-up inv-fade-up--2">
        <span>{{ $heroDay }}</span>
        <span class="nb-when__dot" aria-hidden="true"></span>
        <span>{{ $page->eventDate->format('H:i') }}</span>
    </p>

    @if($page->placeName)
        <p class="nb-place inv-fade-up inv-fade-up--3">{{ $page->placeName }}</p>
    @endif

    @if(!empty($heroMessage))
        <p class="nb-message inv-fade-up inv-fade-up--3">{{ $heroMessage }}</p>
    @endif

    <a href="#contenido" class="inv-hero__scroll nb-hero__scroll">
        {{ $invCopy['scroll_hint'] ?? 'Desliza' }}
        <span class="inv-hero__scroll-line" aria-hidden="true"></span>
    </a>

    {{-- El banco de nubes por donde se baja al resto de la invitación --}}
    <div class="nb-hero__bank" aria-hidden="true">
        @include('invitations.partials.nubes.cloud', ['kind' => 'bank', 'class' => 'nb-hero__bank-back'])
        @include('invitations.partials.nubes.cloud', ['kind' => 'bank', 'class' => 'nb-hero__bank-front'])
    </div>
</header>
