{{--
    Portada de «Bloques de juguete»: un bloque grande, en tres dimensiones, que gira despacio de un
    lado a otro: la foto va en la cara del frente y en las otras se ven la inicial, una estrella y una
    letra, cada cara de un color de la paleta. Debajo, el nombre armado con bloquecitos (una fila por
    palabra), la fecha, la hora, el lugar y el mensaje. Estilos en css/invitation/tendencias/bloques.css.
--}}
@php
    $heroKicker = ($page->welcome['subtitulo'] ?? null) ?: ($invCopy['hero_eyebrow'] ?? 'Baby shower');
    $heroMessage = $page->welcome['mensaje'] ?? '';
    $heroDay = ($page->welcome['fecha_texto'] ?? null)
        ?: \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('l j \d\e F'));
    $nameWords = array_values(array_filter(preg_split('/\s+/u', trim($page->displayName)) ?: []));
    $longestWord = max(array_map('mb_strlen', $nameWords ?: ['Bebé']));
    $initial = mb_strtoupper(mb_substr($page->displayName, 0, 1));
@endphp

<header id="inicio" class="inv-hero bl-hero">
    <p class="bl-kicker inv-fade-up">{{ $heroKicker }}</p>

    <div class="bl-cube-wrap inv-fade-up inv-fade-up--1">
        <div class="bl-cube">
            <span class="bl-cube__face bl-cube__face--front">
                <span class="bl-cube__photo">
                    @include('invitations.partials.tendencias.photo', ['widths' => [480, 768], 'width' => 768, 'sizes' => '(min-width: 640px) 14rem, 60vw'])
                </span>
            </span>
            <span class="bl-cube__face bl-cube__face--right bl-tone--0" aria-hidden="true"><b>{{ $initial }}</b></span>
            <span class="bl-cube__face bl-cube__face--left bl-tone--1" aria-hidden="true"><i class="bl-star"></i></span>
            <span class="bl-cube__face bl-cube__face--top bl-tone--2" aria-hidden="true"><b>A</b></span>
            <span class="bl-cube__face bl-cube__face--back bl-tone--1" aria-hidden="true"></span>
            <span class="bl-cube__face bl-cube__face--bottom bl-tone--0" aria-hidden="true"></span>
        </div>
        <span class="bl-cube__shadow" aria-hidden="true"></span>
    </div>

    {{-- El nombre, en bloquecitos: cada palabra en su fila --}}
    <h1 class="bl-name inv-fade-up inv-fade-up--2" style="--len: {{ $longestWord }}">
        <span class="tr-sr-only">{{ $page->displayName }}</span>
        @php($letterIndex = 0)
        @foreach($nameWords as $word)
            <span class="bl-name__word" aria-hidden="true">
                @foreach(mb_str_split(mb_strtoupper($word)) as $letter)
                    @include('invitations.partials.tendencias.bloques.block', [
                        'letter' => $letter,
                        'tone' => $letterIndex % 3,
                        'class' => 'bl-name__block',
                        'style' => '--i: '.$letterIndex,
                    ])
                    @php($letterIndex++)
                @endforeach
            </span>
        @endforeach
    </h1>

    <p class="bl-when inv-fade-up inv-fade-up--3">
        <span class="bl-when__day">{{ $heroDay }}</span>
        <span class="bl-when__time">{{ $page->eventDate->format('H:i') }}</span>
    </p>
    @if($page->placeName)
        <p class="bl-place inv-fade-up inv-fade-up--3">{{ $page->placeName }}</p>
    @endif

    @if(!empty($heroMessage))
        <p class="bl-message inv-fade-up inv-fade-up--3">{{ $heroMessage }}</p>
    @endif

    <a href="#contenido" class="inv-hero__scroll bl-hero__scroll">
        {{ $invCopy['scroll_hint'] ?? 'Desliza' }}
        <span class="inv-hero__scroll-line" aria-hidden="true"></span>
    </a>
</header>
