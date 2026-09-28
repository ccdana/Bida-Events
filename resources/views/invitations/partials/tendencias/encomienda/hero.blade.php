{{--
    Portada de «Encomienda especial»: la guía del envío. Arriba el título y el número de guía (sale de
    la fecha) con su código de barras (distinto para cada invitación: sale del nombre); la foto va
    pegada con cinta, torcida, y debajo el contenido del envío —su nombre— y los datos: cuándo llega,
    a qué hora y dónde se entrega. Encima, los sellos de «frágil» y «con mucho amor». Estilos en
    css/invitation/tendencias/encomienda.css.
--}}
@php
    $heroKicker = ($page->welcome['subtitulo'] ?? null) ?: ($invCopy['hero_eyebrow'] ?? 'Baby shower');
    $heroMessage = $page->welcome['mensaje'] ?? '';
    $heroDay = ($page->welcome['fecha_texto'] ?? null)
        ?: \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('l j \d\e F'));
    $trackingCode = 'BB-'.$page->eventDate->format('Ymd').'-'.strtoupper(substr(md5($page->displayName), 0, 4));
    // El código de barras: anchos de barra y de espacio, siempre los mismos para este nombre
    $seed = crc32($page->displayName.$page->eventDate->format('Ymd'));
    $bars = [];
    for ($bar = 0; $bar < 46; $bar++) {
        $seed = ($seed * 1103515245 + 12345) & 0x7fffffff;
        $bars[] = [1 + $seed % 3, 1 + intdiv($seed, 7) % 2];
    }
@endphp

<header id="inicio" class="inv-hero en-hero">
    <p class="en-kicker inv-fade-up">{{ $heroKicker }}</p>

    <div class="en-waybill inv-fade-up inv-fade-up--1">
        <div class="en-waybill__head">
            <span class="en-waybill__title">{{ $invCopy['parcel_title'] ?? 'Encomienda especial' }}</span>
            <span class="en-waybill__code">N.º {{ $trackingCode }}</span>
        </div>
        <span class="en-barcode" aria-hidden="true">
            @foreach($bars as [$width, $gap])
                <i style="--w: {{ $width }}; --g: {{ $gap }}"></i>
            @endforeach
        </span>

        {{-- La foto, pegada con cinta --}}
        <figure class="en-photo">
            <span class="en-photo__frame">
                @include('invitations.partials.tendencias.photo', ['widths' => [480, 768], 'width' => 768, 'sizes' => '(min-width: 640px) 15rem, 62vw'])
            </span>
            <span class="en-photo__tape en-photo__tape--1" aria-hidden="true"></span>
            <span class="en-photo__tape en-photo__tape--2" aria-hidden="true"></span>
            <span class="en-stamp en-stamp--care" aria-hidden="true">{{ $invCopy['parcel_care'] ?? 'Con mucho amor' }}</span>
        </figure>

        <p class="en-content">{{ $invCopy['parcel_content'] ?? 'Contenido' }}</p>
        <h1 class="en-name">{{ $page->displayName }}</h1>

        <dl class="en-fields">
            <div class="en-field">
                <dt>{{ $invCopy['parcel_arrival'] ?? 'Llega' }}</dt>
                <dd>{{ $heroDay }}</dd>
            </div>
            <div class="en-field">
                <dt>Hora</dt>
                <dd>{{ $page->eventDate->format('H:i') }}</dd>
            </div>
            @if($page->placeName)
                <div class="en-field en-field--wide">
                    <dt>{{ $invCopy['parcel_address'] ?? 'Entrega en' }}</dt>
                    <dd>{{ $page->placeName }}</dd>
                </div>
            @endif
        </dl>

        <span class="en-stamp en-stamp--fragile" aria-hidden="true">{{ $invCopy['parcel_fragile'] ?? 'Frágil' }}</span>
    </div>

    @if(!empty($heroMessage))
        <p class="en-message inv-fade-up inv-fade-up--3">{{ $heroMessage }}</p>
    @endif

    <a href="#contenido" class="inv-hero__scroll en-hero__scroll">
        {{ $invCopy['scroll_hint'] ?? 'Desliza' }}
        <span class="inv-hero__scroll-line" aria-hidden="true"></span>
    </a>
</header>
