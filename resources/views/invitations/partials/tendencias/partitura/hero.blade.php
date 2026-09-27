{{--
    Portada de «Partitura a dos voces»: la primera página de la partitura de un concierto. Arriba el
    encabezado del programa y la obra («Concierto para dos voces», Op. 1); después la foto y el
    sistema: dos pentagramas unidos por una llave, cada uno con el nombre de su voz escrito a la
    izquierda, como en las partituras de dúo, y la barra final doble. Abajo, las indicaciones de la
    función (con el calderón sobre la fecha: el momento que se sostiene) y las notas al programa.
    Estilos en tendencias/partitura.css.
--}}
@php
    $date = $page->eventDate->locale('es');
    $heroEyebrow = ($page->welcome['subtitulo'] ?? null) ?: ($invCopy['hero_eyebrow'] ?? 'Nos casamos');
    $heroMessage = trim((string) ($page->welcome['mensaje'] ?? ''));
    $heroDay = ($page->welcome['fecha_texto'] ?? null) ?: \Illuminate\Support\Str::ucfirst($date->translatedFormat('l j \d\e F'));
    $voices = array_slice($page->names(), 0, 2);
    // Las dos melodías: la primera sube, la segunda baja, y terminan en la misma nota
    $melodies = [
        [[42, 40], [79, 35], [116, 30], [153, 25], [190, 30], [227, 25], [266, 30]],
        [[42, 20], [79, 25], [116, 30], [153, 35], [190, 30], [227, 35], [266, 30]],
    ];
@endphp

<header id="inicio" class="inv-hero pt-hero">
    <div class="pt-sheet">
        <div class="pt-masthead inv-fade-up">
            <span>{{ $invCopy['score_program'] ?? 'Programa' }}</span>
            <span>{{ $invCopy['score_opus'] ?? 'Op. 1' }}</span>
        </div>

        <p class="pt-work inv-fade-up">{{ $invCopy['score_title'] ?? 'Concierto para dos voces' }}</p>

        @if($page->heroImage)
            <figure class="pt-photo inv-fade-up inv-fade-up--1">
                @include('invitations.partials.tendencias.photo', ['widths' => [480, 768, 1080], 'width' => 1080, 'sizes' => '(min-width: 640px) 18rem, 62vw', 'parallax' => '0.06'])
            </figure>
        @endif

        <p class="pt-eyebrow inv-fade-up inv-fade-up--1">{{ $heroEyebrow }}</p>

        {{-- El sistema: una llave une las dos voces, que se leen juntas --}}
        <h1 class="pt-system inv-fade-up inv-fade-up--2" style="--voices: {{ count($voices) }}">
            @if(count($voices) > 1)
                <svg class="pt-system__brace" viewBox="0 0 20 200" preserveAspectRatio="none" aria-hidden="true" focusable="false">
                    <path d="M18 2 C 6 10, 12 70, 4 100 C 12 130, 6 190, 18 198"/>
                </svg>
            @endif
            @foreach($voices as $voice => $name)
                <span class="pt-part pt-part--{{ $voice + 1 }}">
                    <span class="pt-part__name">{{ $name }}</span>
                    @include('invitations.partials.tendencias.partitura.staff', [
                        'notes' => $melodies[$voice],
                        'stem' => $voice === 0 ? 'up' : 'down',
                        'class' => 'pt-part__staff',
                        'final' => true,
                        'meter' => true,
                    ])
                </span>
                @if(! $loop->last)
                    <span class="tr-sr-only">y</span>
                @endif
            @endforeach
        </h1>

        {{-- Indicaciones de la función: el calderón sobre la fecha --}}
        <dl class="pt-marks inv-fade-up inv-fade-up--3">
            <div class="pt-marks__row">
                <dt>{{ $invCopy['score_date'] ?? 'Función' }}</dt>
                <dd>
                    <svg class="pt-fermata" viewBox="0 0 40 22" aria-hidden="true" focusable="false"><path d="M3 20 C 6 3, 34 3, 37 20"/><circle cx="20" cy="15" r="2.6"/></svg>
                    {{ $heroDay }} · {{ $date->format('H:i') }}
                </dd>
            </div>
            @if($page->placeName)
                <div class="pt-marks__row">
                    <dt>{{ $invCopy['score_hall'] ?? 'Sala' }}</dt>
                    <dd><a href="#ubicacion">{{ $page->placeName }}</a></dd>
                </div>
            @endif
        </dl>

        @if($heroMessage !== '')
            <p class="pt-notes inv-fade-up inv-fade-up--3">{{ $heroMessage }}</p>
        @endif
    </div>

    <a href="#contenido" class="inv-hero__scroll pt-hero__scroll">
        {{ $invCopy['scroll_hint'] ?? 'Desliza' }}
        <span class="inv-hero__scroll-line" aria-hidden="true"></span>
    </a>
</header>
