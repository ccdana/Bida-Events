{{--
    Portada de «A la misma hora»: la esfera del reloj. La foto es el centro de la esfera; alrededor, el
    anillo de marfil con las marcas y los números romanos, la ventanita de la fecha en el lugar del III y la caja
    de latón; encima, las agujas marcando la hora de la boda. Debajo, los nombres, tres subesferas (el
    día, la hora y el año), el lugar y el mensaje. Estilos en css/invitation/tendencias/reloj.css.
--}}
@php
    $heroEyebrow = ($page->welcome['subtitulo'] ?? null) ?: ($invCopy['hero_eyebrow'] ?? 'Nos casamos');
    $heroMessage = $page->welcome['mensaje'] ?? '';
    $date = $page->eventDate->locale('es');
    $heroDay = ($page->welcome['fecha_texto'] ?? null) ?: \Illuminate\Support\Str::ucfirst($date->translatedFormat('l j \d\e F'));
    $hour = (int) $page->eventDate->format('G') % 12;
    $minute = (int) $page->eventDate->format('i');
    $names = $page->names();
@endphp

<header id="inicio" class="inv-hero rl-hero">
    <p class="rl-kicker inv-fade-up">{{ $heroEyebrow }}</p>

    <div class="rl-face inv-fade-up inv-fade-up--1" style="--h: {{ $hour * 30 + $minute * 0.5 }}deg; --m: {{ $minute * 6 }}deg">
        <span class="rl-face__photo">
            @include('invitations.partials.tendencias.photo', ['widths' => [480, 768, 1200], 'width' => 768, 'sizes' => '(min-width: 640px) 15rem, 56vw'])
        </span>
        <span class="rl-face__ring" aria-hidden="true">
            <span class="rl-face__numeral rl-face__numeral--12">XII</span>
            <span class="rl-face__numeral rl-face__numeral--6">VI</span>
            <span class="rl-face__numeral rl-face__numeral--9">IX</span>
        </span>
        <span class="rl-face__date" aria-hidden="true">{{ mb_strtoupper(rtrim($date->translatedFormat('D'), '.')) }} {{ $date->format('j') }}</span>
        <span class="rl-hand rl-hand--hour" aria-hidden="true"></span>
        <span class="rl-hand rl-hand--minute" aria-hidden="true"></span>
        <span class="rl-face__cap" aria-hidden="true"></span>
    </div>

    <h1 class="rl-names inv-fade-up inv-fade-up--2">
        @if(count($names) > 1)
            {{ $names[0] }} <em>&amp;</em> {{ $names[1] }}
        @else
            {{ $page->displayName }}
        @endif
    </h1>

    {{-- Las subesferas: el día, la hora y el año --}}
    <dl class="rl-subdials inv-fade-up inv-fade-up--2">
        <div class="rl-subdial">
            <dt>{{ $invCopy['watch_date'] ?? 'Día' }}</dt>
            <dd>{{ $date->format('j') }} <small>{{ rtrim($date->translatedFormat('M'), '.') }}</small></dd>
        </div>
        <div class="rl-subdial">
            <dt>{{ $invCopy['watch_time'] ?? 'Hora' }}</dt>
            <dd>{{ $page->eventDate->format('H:i') }}</dd>
        </div>
        <div class="rl-subdial">
            <dt>Año</dt>
            <dd>{{ $date->format('Y') }}</dd>
        </div>
    </dl>

    <p class="rl-when inv-fade-up inv-fade-up--3">{{ $heroDay }}</p>
    @if($page->placeName)
        <p class="rl-place inv-fade-up inv-fade-up--3">
            <span>{{ $invCopy['watch_place'] ?? 'Lugar' }}</span>
            {{ $page->placeName }}
        </p>
    @endif

    @if(!empty($heroMessage))
        <p class="rl-message inv-fade-up inv-fade-up--3">{{ $heroMessage }}</p>
    @endif

    <a href="#contenido" class="inv-hero__scroll rl-hero__scroll">
        {{ $invCopy['scroll_hint'] ?? 'Desliza' }}
        <span class="inv-hero__scroll-line" aria-hidden="true"></span>
    </a>
</header>
