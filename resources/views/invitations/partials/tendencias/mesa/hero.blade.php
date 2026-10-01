{{--
    Portada de «Mesa de honor»: el cubierto puesto. Arriba la tarjeta del lugar con los nombres en
    caligrafía; al centro el plato de porcelana con la foto pintada adentro y su filete de oro, entre el
    tenedor y el cuchillo con la cuchara; debajo, el menú de la celebración con el día, la hora y el
    salón, y el mensaje. Al quitarse la servilleta la tarjeta se pone de pie, el plato se apoya y su
    filete gira hasta su lugar, los cubiertos se colocan a los lados y llega el menú (al tocar la
    tarjeta se tambalea: data-poke). Estilos en css/invitation/tendencias/mesa.css.
--}}
@php
    $heroEyebrow = ($page->welcome['subtitulo'] ?? null) ?: ($invCopy['hero_eyebrow'] ?? 'Nos casamos');
    $heroMessage = $page->welcome['mensaje'] ?? '';
    $heroDay = ($page->welcome['fecha_texto'] ?? null)
        ?: \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('l j \d\e F'));
@endphp

<header id="inicio" class="inv-hero ms-hero">
    <p class="ms-kicker">{{ $heroEyebrow }}</p>

    <div class="ms-tent" data-poke="tip">
        <h1 class="ms-tent__names">{{ $page->displayName }}</h1>
    </div>

    <div class="ms-hero__setting">
        @include('invitations.partials.tendencias.mesa.cutlery', ['piece' => 'fork', 'class' => 'ms-hero__fork'])
        <div class="ms-plate ms-plate--hero">
            <span class="ms-plate__photo">
                @include('invitations.partials.tendencias.photo', ['widths' => [480, 768, 1200], 'width' => 768, 'sizes' => '(min-width: 640px) 14rem, 52vw'])
            </span>
        </div>
        <span class="ms-hero__right" aria-hidden="true">
            @include('invitations.partials.tendencias.mesa.cutlery', ['piece' => 'knife'])
            @include('invitations.partials.tendencias.mesa.cutlery', ['piece' => 'spoon'])
        </span>
    </div>

    {{-- El menú de la celebración --}}
    <div class="ms-menu">
        <p class="ms-menu__title">{{ $invCopy['table_menu'] ?? 'Menú de la celebración' }}</p>
        <dl class="ms-menu__fields">
            <div>
                <dt>Día</dt>
                <dd>{{ $heroDay }}</dd>
            </div>
            <div>
                <dt>Hora</dt>
                <dd>{{ $page->eventDate->format('H:i') }}</dd>
            </div>
            @if($page->placeName)
                <div>
                    <dt>Salón</dt>
                    <dd>{{ $page->placeName }}</dd>
                </div>
            @endif
        </dl>
    </div>

    @if(!empty($heroMessage))
        <p class="ms-message">{{ $heroMessage }}</p>
    @endif

    <a href="#contenido" class="inv-hero__scroll ms-hero__scroll">
        {{ $invCopy['scroll_hint'] ?? 'Desliza' }}
        <span class="inv-hero__scroll-line" aria-hidden="true"></span>
    </a>
</header>
