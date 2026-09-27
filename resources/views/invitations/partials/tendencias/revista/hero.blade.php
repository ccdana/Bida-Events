{{--
    Portada de «Edición especial»: la portada de la revista de colección. Arriba la línea de la
    edición y el nombre de la revista a todo lo ancho (Promoción); la foto de portada con el nombre
    del graduado como estrella de tapa y los titulares, que son enlaces a sus páginas (la fiesta,
    dónde será, qué ponerse, el programa); el sticker de «Edición de colección» y el código de
    barras con la fecha. Debajo, el índice de la edición y la carta del editor (el mensaje).
    Los números de página son los mismos que llevan las secciones (tendencias/revista.css).
--}}
@php
    $date = $page->eventDate->locale('es');
    $year = $date->format('Y');
    $heroEyebrow = ($page->welcome['subtitulo'] ?? null) ?: ($invCopy['hero_eyebrow'] ?? 'Me gradúo');
    $heroMessage = trim((string) ($page->welcome['mensaje'] ?? ''));
    $heroDay = ($page->welcome['fecha_texto'] ?? null) ?: \Illuminate\Support\Str::ucfirst($date->translatedFormat('l j \d\e F'));
    $pageWord = $invCopy['mag_page'] ?? 'pág.';

    // Número de página de cada sección: la portada es la 1 y cada sección ocupa una doble página
    $contents = array_values(array_filter($page->navItems(), fn (array $item) => $item['id'] !== 'inicio'));
    $pages = [];
    foreach ($contents as $position => $item) {
        $pages[$item['id']] = ($position + 1) * 2;
    }
    $pageOf = fn (string $id) => isset($pages[$id]) ? $pageWord.' '.$pages[$id] : null;

    // Los titulares de tapa: solo los que tienen página en esta edición
    $coverLines = array_values(array_filter([
        isset($pages['itinerario']) ? ['itinerario', $invCopy['mag_agenda'] ?? 'El programa completo'] : null,
        isset($pages['dress-code']) ? ['dress-code', $invCopy['mag_style'] ?? 'Qué ponerte'] : null,
        isset($pages['galeria']) ? ['galeria', $invCopy['gallery_eyebrow'] ?? 'Fotorreportaje'] : null,
    ]));

    // Código de barras: las barras salen de los dígitos de la fecha (AAAAMMDD)
    $barcodeDigits = str_split($date->format('Ymd'));
    $barX = 0;

    // El cabezal de cada página («Promoción 2026 · pág. 4») lo arma CSS con estos textos
    $cssText = fn (string $text) => trim(str_replace(['<', '>', '"', '\\'], '', $text));
@endphp

<style>
    .inv-revista {
        --rv-running: "{{ $cssText(($invCopy['mag_name'] ?? 'Promoción').' '.$year) }}";
        --rv-page-word: "{{ $cssText($pageWord) }}";
    }
</style>

<header id="inicio" class="inv-hero rv-hero">
    <article class="rv-cover inv-fade-up">
        <div class="rv-cover__top">
            <p class="rv-cover__issue">
                <span>{{ $invCopy['mag_issue'] ?? 'N.º 1 · Edición especial' }}</span>
                <span>{{ \Illuminate\Support\Str::ucfirst($date->translatedFormat('F Y')) }}</span>
            </p>
            <p class="rv-masthead" data-fit data-fit-max="190" style="--fit-fallback: clamp(3.2rem, 20vw, 8rem)">{{ $invCopy['mag_name'] ?? 'Promoción' }}</p>
            <span class="rv-masthead__year">{{ $year }}</span>
        </div>

        <figure @class(['rv-cover__photo', 'is-typographic' => ! $page->heroImage])>
            @if($page->heroImage)
                @include('invitations.partials.tendencias.photo', ['widths' => [480, 768, 1080], 'width' => 1080, 'sizes' => '(min-width: 640px) 30rem, 100vw', 'parallax' => '0.05'])
            @else
                {{-- Sin foto, la tapa es tipográfica: el año de la promoción, gigante --}}
                <span class="rv-cover__type" aria-hidden="true">
                    <span>{{ substr($year, 0, 2) }}</span><span>{{ substr($year, 2) }}</span>
                </span>
            @endif
            <span class="rv-cover__scrim" aria-hidden="true"></span>

            {{-- La fiesta del año: el titular principal, con el resaltador sobre la fecha --}}
            <a href="{{ isset($pages['ubicacion']) ? '#ubicacion' : '#contenido' }}" class="rv-lead">
                <span class="rv-lead__kicker">{{ $invCopy['mag_party'] ?? 'La fiesta del año' }}</span>
                <span class="rv-lead__date"><mark>{{ $heroDay }}</mark> <mark>{{ $date->format('H:i') }}</mark></span>
                @if($page->placeName)
                    <span class="rv-lead__place">{{ $invCopy['mag_where'] ?? 'Dónde será' }}: {{ $page->placeName }}</span>
                @endif
            </a>

            <span class="rv-sticker" aria-hidden="true">{{ $invCopy['intro_eyebrow'] ?? 'Edición de colección' }}</span>

            <figcaption class="rv-star">
                <span class="rv-star__kicker">{{ $invCopy['mag_exclusive'] ?? 'Exclusiva' }} · {{ $heroEyebrow }}</span>
                <h1 class="rv-star__name">{{ $page->displayName }}</h1>
            </figcaption>
        </figure>

        <div class="rv-cover__foot">
            @if(count($coverLines))
                <ul class="rv-lines">
                    @foreach($coverLines as [$id, $line])
                        <li><a href="#{{ $id }}">{{ $line }} <span>{{ $pageOf($id) }}</span></a></li>
                    @endforeach
                </ul>
            @endif
            <svg class="rv-barcode" viewBox="0 0 120 48" aria-hidden="true" focusable="false">
                @foreach($barcodeDigits as $digit)
                    @php($barWidth = 1 + ((int) $digit % 3))
                    <rect x="{{ $barX }}" y="0" width="{{ $barWidth }}" height="38"/>
                    @php($barX += $barWidth + 2 + ((int) $digit % 2))
                    <rect x="{{ $barX }}" y="0" width="1" height="38"/>
                    @php($barX += 3)
                @endforeach
                <text x="0" y="47">{{ $date->format('d m Y') }}</text>
            </svg>
        </div>
    </article>

    {{-- El índice de la edición: cada sección con su página --}}
    @if(count($contents))
        <nav class="rv-contents inv-fade-up inv-fade-up--2" aria-label="{{ $invCopy['mag_contents'] ?? 'En esta edición' }}">
            <p class="rv-contents__title">{{ $invCopy['mag_contents'] ?? 'En esta edición' }}</p>
            <ol class="rv-contents__list">
                @foreach($contents as $item)
                    <li>
                        <a href="#{{ $item['id'] }}">
                            <span class="rv-contents__page">{{ $pages[$item['id']] }}</span>
                            <span class="rv-contents__label">{{ $item['label'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ol>
        </nav>
    @endif

    @if($heroMessage !== '')
        <section class="rv-letter inv-fade-up inv-fade-up--3" aria-label="{{ $invCopy['mag_letter'] ?? 'Carta del editor' }}">
            <p class="rv-letter__title">{{ $invCopy['mag_letter'] ?? 'Carta del editor' }}</p>
            <p class="rv-letter__text">{{ $heroMessage }}</p>
            <p class="rv-letter__sign">— {{ $page->displayName }}</p>
        </section>
    @endif

    <a href="#contenido" class="inv-hero__scroll rv-hero__scroll">
        {{ $invCopy['scroll_hint'] ?? 'Desliza' }}
        <span class="inv-hero__scroll-line" aria-hidden="true"></span>
    </a>
</header>
