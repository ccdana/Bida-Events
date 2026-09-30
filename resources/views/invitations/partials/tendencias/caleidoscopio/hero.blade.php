{{--
    Portada de «Caleidoscopio»: un «XV» enorme pintado con los colores del prisma y, delante, la foto
    en un hexágono con su borde de cristal. Debajo, su nombre (la última palabra en cursiva) y tres
    facetas con el día, la hora y el lugar. Estilos en css/invitation/tendencias/caleidoscopio.css.
--}}
@php
    $heroEyebrow = ($page->welcome['subtitulo'] ?? null) ?: ($invCopy['hero_eyebrow'] ?? 'Mis XV años');
    $heroMessage = $page->welcome['mensaje'] ?? '';
    $heroDay = ($page->welcome['fecha_texto'] ?? null)
        ?: \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('l j \d\e F'));
    // El nombre: la última palabra en cursiva, como el destello del cristal
    $nameWords = preg_split('/\s+/u', trim($page->displayName)) ?: [$page->displayName];
    $nameLast = count($nameWords) > 1 ? array_pop($nameWords) : null;
    $nameFirst = implode(' ', $nameWords);
@endphp

<header id="inicio" class="inv-hero ka-hero">
    <p class="ka-kicker inv-fade-up">{{ $heroEyebrow }}</p>

    <div class="ka-emblem inv-fade-up inv-fade-up--1">
        <span class="ka-emblem__xv" aria-hidden="true">XV</span>
        <figure class="ka-hex">
            <span class="ka-hex__rim" aria-hidden="true"></span>
            <span class="ka-hex__photo">
                @include('invitations.partials.tendencias.photo', ['widths' => [480, 768, 1200], 'width' => 768, 'sizes' => '(min-width: 640px) 16rem, 60vw'])
            </span>
        </figure>
    </div>

    <h1 class="ka-name inv-fade-up inv-fade-up--2">
        {{ $nameFirst }}
        @if($nameLast)
            <em>{{ $nameLast }}</em>
        @endif
    </h1>

    <dl class="ka-facets inv-fade-up inv-fade-up--2">
        <div class="ka-facet">
            <dt>{{ $invCopy['scope_day'] ?? 'Día' }}</dt>
            <dd>{{ $heroDay }}</dd>
        </div>
        <div class="ka-facet">
            <dt>{{ $invCopy['scope_time'] ?? 'Hora' }}</dt>
            <dd>{{ $page->eventDate->format('H:i') }}</dd>
        </div>
        @if($page->placeName)
            <div class="ka-facet ka-facet--wide">
                <dt>{{ $invCopy['scope_place'] ?? 'Lugar' }}</dt>
                <dd>{{ $page->placeName }}</dd>
            </div>
        @endif
    </dl>

    @if(!empty($heroMessage))
        <p class="ka-message inv-fade-up inv-fade-up--3">{{ $heroMessage }}</p>
    @endif

    <a href="#contenido" class="inv-hero__scroll ka-hero__scroll">
        {{ $invCopy['scroll_hint'] ?? 'Desliza' }}
        <span class="inv-hero__scroll-line" aria-hidden="true"></span>
    </a>
</header>
