{{--
    Portada de «Tendedero»: tres cordeles cruzan la portada. En el de arriba cuelga la ropita del bebé
    con sus pinzas: el gorrito, el enterito con el nombre estampado y las medias; en el segundo, la
    foto como una instantánea, y en el tercero las etiquetas de papel con la fecha, la hora y el lugar, que se mecen
    cada una a su ritmo. Debajo, el mensaje en un paño cortado con tijera de zigzag. Estilos en
    tendencias/tendedero.css.
--}}
@php
    $date = $page->eventDate->locale('es');
    $heroEyebrow = ($page->welcome['subtitulo'] ?? null) ?: ($invCopy['hero_eyebrow'] ?? 'Baby shower');
    $heroMessage = trim((string) ($page->welcome['mensaje'] ?? ''));
    $heroDay = ($page->welcome['fecha_texto'] ?? null) ?: \Illuminate\Support\Str::ucfirst($date->translatedFormat('l j \d\e F'));
    $tags = array_values(array_filter([
        [$invCopy['line_date'] ?? 'Fecha', $heroDay, null],
        [$invCopy['line_time'] ?? 'Hora', $date->format('H:i'), null],
        $page->placeName ? [$invCopy['line_place'] ?? 'Lugar', $page->placeName, '#ubicacion'] : null,
    ]));
@endphp

<header id="inicio" class="inv-hero td-hero">
    <p class="td-hero__eyebrow inv-fade-up">{{ $heroEyebrow }}</p>

    {{-- El primer cordel: la ropita --}}
    <div class="td-row td-row--clothes inv-fade-up">
        <span class="td-line" aria-hidden="true"></span>
        @include('invitations.partials.tendencias.tendedero.garment', ['kind' => 'hat', 'class' => 'td-row__item'])
        <h1 class="td-row__item td-row__item--name">
            @include('invitations.partials.tendencias.tendedero.garment', ['kind' => 'onesie', 'text' => $page->displayName, 'fit' => true])
        </h1>
        @include('invitations.partials.tendencias.tendedero.garment', ['kind' => 'socks', 'class' => 'td-row__item'])
    </div>

    {{-- El segundo cordel: la foto, como una instantánea --}}
    @if($page->heroImage)
        <div class="td-row td-row--photo inv-fade-up inv-fade-up--1">
            <span class="td-line" aria-hidden="true"></span>
            <figure class="td-polaroid">
                <span class="td-polaroid__photo">
                    @include('invitations.partials.tendencias.photo', ['widths' => [360, 720], 'width' => 720, 'sizes' => '12rem'])
                </span>
                <span class="td-pin" aria-hidden="true"></span>
            </figure>
        </div>
    @endif

    {{-- El tercer cordel: las etiquetas con los datos --}}
    <div class="td-row td-row--tags inv-fade-up inv-fade-up--2">
        <span class="td-line" aria-hidden="true"></span>
        <dl class="td-tags">
            @foreach($tags as [$label, $value, $link])
                <div class="td-tag" style="--t: {{ 4.2 + $loop->index * 0.6 }}s">
                    <dt>{{ $label }}</dt>
                    <dd>
                        @if($link)
                            <a href="{{ $link }}">{{ $value }}</a>
                        @else
                            {{ $value }}
                        @endif
                    </dd>
                </div>
            @endforeach
        </dl>
    </div>

    @if($heroMessage !== '')
        <p class="td-cloth td-hero__message inv-fade-up inv-fade-up--3">{{ $heroMessage }}</p>
    @endif

    <a href="#contenido" class="inv-hero__scroll td-hero__scroll">
        {{ $invCopy['scroll_hint'] ?? 'Desliza' }}
        <span class="inv-hero__scroll-line" aria-hidden="true"></span>
    </a>
</header>
