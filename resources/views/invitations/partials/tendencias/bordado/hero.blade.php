{{--
    Portada de «Bordado a mano»: el bastidor grande con la foto en el centro del lino, rodeada de una
    corona bordada (el tallo en punto atrás, hojitas de satén, florcitas en punto margarita y nuditos)
    y un pespunte alrededor de la foto. Debajo, el nombre en hilo de satén, la fecha en una etiqueta
    cosida a mano, el lugar y el mensaje. Estilos en css/invitation/tendencias/bordado.css.
--}}
@php
    $heroKicker = ($page->welcome['subtitulo'] ?? null) ?: ($invCopy['intro_eyebrow'] ?? 'Bordado con amor');
    $heroMessage = $page->welcome['mensaje'] ?? '';
    $heroDay = ($page->welcome['fecha_texto'] ?? null)
        ?: \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('l j \d\e F \d\e Y'));

    // La corona: hojitas a lo largo del tallo, alternando hacia afuera y hacia adentro
    $wreathLeaves = [];
    for ($angle = 0; $angle < 360; $angle += 13) {
        $side = ($angle / 13) % 2 === 0 ? 1 : -1;
        $radians = deg2rad($angle);
        $wreathLeaves[] = [round(160 + cos($radians) * 128, 1), round(160 + sin($radians) * 128, 1), $angle + 90 + $side * 38];
    }
    // Las flores de la corona: ángulo, tamaño y si es del color de las flores o del hilo del nombre
    $wreathFlowers = [[-62, 1.05, false], [-18, 0.8, true], [30, 1, false], [78, 0.85, true], [128, 1.1, false], [178, 0.8, true], [222, 1, false], [262, 0.85, true]];
@endphp

<header id="inicio" class="inv-hero bd-hero">
    <p class="bd-kicker inv-fade-up">{{ $heroKicker }}</p>

    <div class="bd-hoop bd-hero__hoop inv-fade-up inv-fade-up--1">
        <span class="bd-hoop__clasp" aria-hidden="true"><i></i></span>
        <span class="bd-hoop__cloth">
            <svg class="bd-wreath" viewBox="0 0 320 320" aria-hidden="true" focusable="false">
                <circle class="bd-wreath__stem" cx="160" cy="160" r="128"/>
                @foreach($wreathLeaves as $index => [$x, $y, $rotate])
                    <path class="bd-wreath__leaf" style="--i: {{ $index }}" d="M0 0 C3.5 -4.5 10 -5 14 0 C10 5 3.5 4.5 0 0 Z" transform="translate({{ $x }} {{ $y }}) rotate({{ $rotate }})"/>
                @endforeach
                @foreach($wreathFlowers as $index => [$angle, $size, $isThread])
                    @include('invitations.partials.tendencias.bordado.flower', [
                        'x' => round(160 + cos(deg2rad($angle)) * 128, 1),
                        'y' => round(160 + sin(deg2rad($angle)) * 128, 1),
                        'size' => $size,
                        'rotate' => $angle * 2,
                        'class' => $isThread ? 'is-thread' : '',
                        'step' => $index,
                    ])
                @endforeach
                <circle class="bd-wreath__backstitch" cx="160" cy="160" r="104"/>
            </svg>

            <span class="bd-hero__photo">
                @include('invitations.partials.tendencias.photo', ['widths' => [480, 768], 'width' => 768, 'sizes' => '(min-width: 640px) 13rem, 58vw'])
            </span>
        </span>
    </div>

    <h1 class="bd-name inv-fade-up inv-fade-up--2"><span class="bd-satin">{{ $page->displayName }}</span></h1>

    {{-- La fecha, en una etiqueta de tela cosida con pespunte --}}
    <p class="bd-tag inv-fade-up inv-fade-up--3">
        <span class="bd-tag__day">{{ $heroDay }}</span>
        <span class="bd-tag__time">a las {{ $page->eventDate->format('H:i') }}</span>
    </p>

    @if($page->placeName)
        <p class="bd-place inv-fade-up inv-fade-up--3">{{ $page->placeName }}</p>
    @endif

    @if(!empty($heroMessage))
        <p class="bd-message inv-fade-up inv-fade-up--3">{{ $heroMessage }}</p>
    @endif

    <a href="#contenido" class="inv-hero__scroll bd-hero__scroll">
        {{ $invCopy['scroll_hint'] ?? 'Desliza' }}
        <span class="inv-hero__scroll-line" aria-hidden="true"></span>
    </a>
</header>
