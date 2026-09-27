{{--
    Portada de «Móvil de cuna»: arriba, el móvil que cuelga sobre la cuna —el gancho, la varilla de
    madera con sus cuentas y cinco figuras de fieltro cosidas (la luna, la estrella, la vela del
    bautismo, la casita y el corderito) que se mecen cada una a su ritmo—. Debajo, el banderín de
    fieltro con el nombre bordado, la foto en un marco redondo con costura y una tarjeta cosida con la
    fecha, la hora y el lugar. De aquí baja el hilo del que cuelga cada sección. Estilos en
    tendencias/movil.css.
--}}
@php
    $date = $page->eventDate->locale('es');
    $heroEyebrow = ($page->welcome['subtitulo'] ?? null) ?: ($invCopy['hero_eyebrow'] ?? 'Mi bautizo');
    $heroMessage = trim((string) ($page->welcome['mensaje'] ?? ''));
    $heroDay = ($page->welcome['fecha_texto'] ?? null) ?: \Illuminate\Support\Str::ucfirst($date->translatedFormat('l j \d\e F'));
@endphp

@include('invitations.partials.tendencias.movil.shapes')

<header id="inicio" class="inv-hero mv-hero">
    @include('invitations.partials.tendencias.movil.mobile', ['class' => 'inv-fade-up'])

    {{-- El banderín con el nombre bordado, colgado de dos hilos --}}
    <div class="mv-pennant inv-fade-up inv-fade-up--1">
        <p class="mv-pennant__eyebrow">{{ $heroEyebrow }}</p>
        <h1 class="mv-pennant__name">{{ $page->displayName }}</h1>
    </div>

    @if($page->heroImage)
        <figure class="mv-photo inv-fade-up inv-fade-up--2">
            @include('invitations.partials.tendencias.photo', ['widths' => [360, 720, 1080], 'width' => 1080, 'sizes' => '(min-width: 640px) 16rem, 60vw'])
        </figure>
    @endif

    <div class="mv-card mv-hero__facts inv-fade-up inv-fade-up--3">
        <dl class="mv-facts">
            <div>
                <dt>@include('invitations.partials.tendencias.movil.figure', ['shape' => 'star', 'class' => 'mv-felt--2 mv-facts__icon'])<span class="tr-sr-only">Fecha</span></dt>
                <dd>{{ $heroDay }}</dd>
            </div>
            <div>
                <dt>@include('invitations.partials.tendencias.movil.figure', ['shape' => 'candle', 'class' => 'mv-felt--1 mv-facts__icon'])<span class="tr-sr-only">Hora</span></dt>
                <dd>{{ $date->format('H:i') }}</dd>
            </div>
            @if($page->placeName)
                <div>
                    <dt>@include('invitations.partials.tendencias.movil.figure', ['shape' => 'house', 'class' => 'mv-felt--2 mv-facts__icon'])<span class="tr-sr-only">Lugar</span></dt>
                    <dd><a href="#ubicacion">{{ $page->placeName }}</a></dd>
                </div>
            @endif
        </dl>
        @if($heroMessage !== '')
            <p class="mv-hero__message">{{ $heroMessage }}</p>
        @endif
    </div>

    <a href="#contenido" class="inv-hero__scroll mv-hero__scroll">
        {{ $invCopy['scroll_hint'] ?? 'Desliza' }}
        <span class="inv-hero__scroll-line" aria-hidden="true"></span>
    </a>
</header>
