{{--
    Portada de «Cuento desplegable»: la primera página del cuento. Arriba el titulillo con el nombre del
    libro; la foto es la lámina, dentro de un arco con dos capas de papel detrás que asoman como un
    desplegable; debajo, su nombre como título del cuento, «Érase una vez…» y el mensaje con su letra
    capitular. Al pie, el colofón con la fecha, la hora y el lugar, y el número de página. Al abrirse
    el libro la lámina se levanta capa por capa como un desplegable, el título se pone de pie y el
    texto aparece línea a línea; al hacer scroll las capas de papel se separan (data-parallax). Estilos
    en css/invitation/tendencias/cuento.css.
--}}
@php
    $heroEyebrow = ($page->welcome['subtitulo'] ?? null) ?: ($invCopy['hero_eyebrow'] ?? 'Mis XV años');
    $heroMessage = trim((string) ($page->welcome['mensaje'] ?? ''));
    $heroDay = ($page->welcome['fecha_texto'] ?? null)
        ?: \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('l j \d\e F'));
    $firstName = \Illuminate\Support\Str::of($page->displayName)->explode(' ')->first();
    // La palabra antes del número de cada capítulo (CSS counter)
    $chapterLabel = trim(str_replace(['<', '>', '"', '\\'], '', (string) ($invCopy['book_chapter'] ?? 'Capítulo')));
@endphp

<style>
    .inv-cuento { --cu-chapter-label: "{{ $chapterLabel }}"; }
</style>

<header id="inicio" class="inv-hero cu-hero">
    <div class="cu-leaf">
        <p class="cu-running">
            <span>{{ $invCopy['book_title'] ?? 'El cuento de' }} {{ $firstName }}</span>
            <span>{{ $chapterLabel }} I</span>
        </p>

        {{-- La lámina: la foto en su arco, con las capas de papel del desplegable detrás --}}
        <figure class="cu-plate">
            <span class="cu-plate__layer cu-plate__layer--back" data-parallax="-0.06" aria-hidden="true"></span>
            <span class="cu-plate__layer cu-plate__layer--mid" data-parallax="-0.03" aria-hidden="true"></span>
            <span class="cu-plate__photo">
                @include('invitations.partials.tendencias.photo', ['widths' => [480, 768, 1200], 'width' => 768, 'sizes' => '(min-width: 640px) 17rem, 62vw'])
            </span>
            <figcaption class="cu-plate__caption">{{ $invCopy['book_plate'] ?? 'Lámina' }} I</figcaption>
        </figure>

        <p class="cu-kicker">{{ $heroEyebrow }}</p>
        <h1 class="cu-title">{{ $page->displayName }}</h1>
        <span class="cu-fleuron" aria-hidden="true"></span>

        <p class="cu-opening">{{ $invCopy['book_opening'] ?? 'Érase una vez…' }}</p>
        @if($heroMessage !== '')
            <p class="cu-message">{{ $heroMessage }}</p>
        @endif

        {{-- El colofón: cuándo y dónde --}}
        <dl class="cu-colophon">
            <div>
                <dt>Fecha</dt>
                <dd>{{ $heroDay }}</dd>
            </div>
            <div>
                <dt>Hora</dt>
                <dd>{{ $page->eventDate->format('H:i') }}</dd>
            </div>
            @if($page->placeName)
                <div>
                    <dt>Lugar</dt>
                    <dd>{{ $page->placeName }}</dd>
                </div>
            @endif
        </dl>

        <span class="cu-folio" aria-hidden="true">1</span>
    </div>

    <a href="#contenido" class="inv-hero__scroll cu-hero__scroll">
        {{ $invCopy['scroll_hint'] ?? 'Desliza' }}
        <span class="inv-hero__scroll-line" aria-hidden="true"></span>
    </a>
</header>
