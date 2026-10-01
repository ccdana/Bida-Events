{{--
    Portada de «El cambio de zapatos»: la caja abierta vista desde arriba. Arriba, su nombre como la
    marca de la zapatería con la línea de la colección; en la caja, el papel de seda abierto hacia los
    dos lados, la foto al centro como una lámina y a cada lado una de sus zapatillas (la izquierda con
    la punta hacia arriba y su nombre grabado; la derecha hacia abajo). Debajo, la etiqueta del costado
    de la caja con el modelo, la talla, la fecha, la hora y el lugar, y su código de barras; al final,
    el mensaje. Al abrirse la caja, el cajón se asienta, el papel se abre, sube la foto, se apoyan las
    zapatillas y se pega la etiqueta; al hacer scroll las zapatillas van un poco por delante
    (data-parallax). Estilos en css/invitation/tendencias/zapatos.css.
--}}
@php
    $heroEyebrow = ($page->welcome['subtitulo'] ?? null) ?: ($invCopy['hero_eyebrow'] ?? 'Mis XV años');
    $heroMessage = $page->welcome['mensaje'] ?? '';
    $heroDay = ($page->welcome['fecha_texto'] ?? null)
        ?: \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('l j \d\e F'));
    $firstName = \Illuminate\Support\Str::of($page->displayName)->explode(' ')->first();
    // El código de barras sale de la fecha: siempre el mismo para esta invitación
    $barcode = $page->eventDate->format('Ymd').$page->eventDate->format('Hi');
    // La palabra antes del número de cada caja (CSS counter)
    $refLabel = trim(str_replace(['<', '>', '"', '\\'], '', (string) ($invCopy['shoe_ref'] ?? 'Ref.')));
    // El papel de las etiquetas y el de seda siguen el modo de la paleta: claros sobre un fondo claro y
    // apenas más claros que el fondo si es oscuro. La tinta de las etiquetas la calcula el servidor.
    $contrast = \App\Support\ColorContrast::class;
    $shoeDark = $contrast::luminance($page->colors['background']) < 0.3;
    $shoePaper = $shoeDark
        ? $contrast::mix($page->colors['background'], '#FFFFFF', 0.86)
        : $contrast::mix($page->colors['background'], '#FFFFFF', 0.3);
    // El papel de seda lleva todo el texto de las secciones: el acento se aclara (o se apaga, en una
    // paleta oscura) hasta que el texto de la paleta se lea encima con AA
    $tissueBase = $shoeDark ? $page->colors['background'] : '#FFFFFF';
    $tissueWeight = $shoeDark ? 0.22 : 0.55;
    $shoeTissue = $contrast::mix($page->colors['accent'], $tissueBase, $tissueWeight);
    while ($tissueWeight > 0 && $contrast::ratio($page->colors['text'], $shoeTissue) < $contrast::AA_TEXT) {
        $tissueWeight = max(0, $tissueWeight - 0.05);
        $shoeTissue = $contrast::mix($page->colors['accent'], $tissueBase, $tissueWeight);
    }
@endphp

<style>
    .inv-zapatos {
        --zp-ref-label: "{{ $refLabel }}";
        --zp-paper: {{ $shoePaper }};
        --zp-paper-ink: {{ $contrast::inkOn($shoePaper, $page->colors['text'], $page->colors['background']) }};
        --zp-tissue-base: {{ $shoeTissue }};
        --zp-crinkle: {{ $shoeDark ? 'rgb(255 255 255 / 0.07)' : 'rgb(255 255 255 / 0.4)' }};
    }
</style>

<header id="inicio" class="inv-hero zp-hero">
    <p class="zp-kicker">{{ $heroEyebrow }}</p>
    <h1 class="zp-brand">{{ $page->displayName }}</h1>
    <p class="zp-brand__line">{{ $invCopy['shoe_line'] ?? 'Colección XV' }} · {{ $page->eventDate->format('Y') }}</p>

    {{-- La caja abierta: el papel de seda hacia los lados, la foto y las dos zapatillas --}}
    <div class="zp-open">
        <span class="zp-open__tissue zp-open__tissue--left" aria-hidden="true"></span>
        <span class="zp-open__tissue zp-open__tissue--right" aria-hidden="true"></span>
        <div class="zp-open__tray">
            <span class="zp-open__shoe zp-open__shoe--left" data-parallax="0.05">
                @include('invitations.partials.tendencias.zapatos.shoe', ['id' => 'zp-hero-left', 'name' => $firstName])
            </span>
            <figure class="zp-open__photo">
                <span class="zp-open__print">
                    @include('invitations.partials.tendencias.photo', ['widths' => [480, 768, 1200], 'width' => 768, 'sizes' => '(min-width: 640px) 15rem, 48vw'])
                </span>
            </figure>
            <span class="zp-open__shoe zp-open__shoe--right" data-parallax="0.05">
                @include('invitations.partials.tendencias.zapatos.shoe', ['id' => 'zp-hero-right'])
            </span>
        </div>
    </div>

    {{-- La etiqueta del costado de la caja: cuándo y dónde --}}
    <div class="zp-label">
        <p class="zp-label__head">
            <span>{{ $invCopy['shoe_model'] ?? 'Modelo' }}</span>
            <strong>{{ $invCopy['shoe_model_name'] ?? 'Mis primeros tacones' }}</strong>
        </p>
        <dl class="zp-label__fields">
            <div>
                <dt>{{ $invCopy['shoe_date'] ?? 'Fecha' }}</dt>
                <dd>{{ $heroDay }}</dd>
            </div>
            <div>
                <dt>{{ $invCopy['shoe_time'] ?? 'Hora' }}</dt>
                <dd>{{ $page->eventDate->format('H:i') }}</dd>
            </div>
            @if($page->placeName)
                <div class="zp-label__wide">
                    <dt>{{ $invCopy['shoe_place'] ?? 'Lugar' }}</dt>
                    <dd>{{ $page->placeName }}</dd>
                </div>
            @endif
        </dl>
        <div class="zp-label__foot" aria-hidden="true">
            <span class="zp-label__bars"></span>
            <span class="zp-label__code">{{ $barcode }}</span>
            <span class="zp-label__size"><small>{{ $invCopy['shoe_size'] ?? 'Talla' }}</small>XV</span>
        </div>
    </div>

    @if(!empty($heroMessage))
        <p class="zp-message">{{ $heroMessage }}</p>
    @endif

    <a href="#contenido" class="inv-hero__scroll zp-hero__scroll">
        {{ $invCopy['scroll_hint'] ?? 'Desliza' }}
        <span class="inv-hero__scroll-line" aria-hidden="true"></span>
    </a>
</header>
