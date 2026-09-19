{{--
    Escenario fijo detrás de los cuatro actos (decorativo, aria-hidden). Capas, de atrás hacia adelante:
    cielo de la noche con unas pocas estrellas quietas (Actos I-II, para que los lados no se sientan
    vacíos), cielo profundo (Acto III), cielo a lo Van Gogh (Acto IV: viento en pinceladas, dos
    remolinos, estrellas con halo, ciprés), la luna y el agua con su reflejo.
    resources/js/cards/historia/stage.js mueve cada capa según el acto en que va la lectura; sin
    JavaScript queda la primera escena quieta.
--}}
@php
    // Espiral de Arquímedes como hebras de pinceladas: cada hebra desplazada, con su color y su trazo
    $spiral = function (float $start, float $spread): string {
        $points = [];
        for ($theta = 0.0; $theta <= 5 * M_PI; $theta += 0.22) {
            $radius = $spread + 5.4 * $theta;
            $points[] = sprintf('%.1f %.1f', 100 + $radius * cos($theta + $start), 100 + $radius * sin($theta + $start));
        }

        return 'M'.implode(' L', $points);
    };
    $strands = [
        ['start' => 0.0, 'spread' => 4, 'class' => 'is-pale'],
        ['start' => 0.9, 'spread' => 9, 'class' => 'is-mid'],
        ['start' => 1.8, 'spread' => 14, 'class' => 'is-deep'],
    ];
    $stars = [
        [10, 12, 0.9, 0.0], [30, 6, 0.6, 1.2], [80, 8, 0.7, 0.6], [90, 30, 1.1, 2.1], [6, 44, 0.7, 1.7],
        [64, 20, 0.5, 0.3], [46, 10, 0.75, 2.6], [94, 52, 0.6, 0.9], [22, 30, 1.0, 1.4],
    ];
    $glade = [30, 44, 36, 52, 40, 58, 34, 46, 26, 38, 20, 28, 14];
    // Fuera de la columna central de texto (max-width 33rem): quietas, sin la elaboración de las del Acto IV
    $ambientStars = [
        [7, 13, 0.8], [16, 33, 0.5], [4, 47, 0.65],
        [93, 10, 0.7], [84, 29, 0.5], [95, 45, 0.6],
    ];
@endphp

<div class="story-stage" data-story-stage aria-hidden="true">
    <div class="story-sky story-sky--dusk"></div>

    <div class="story-sky story-sky--ambient">
        @foreach($ambientStars as [$x, $y, $size])
            <span class="story-dot" style="--x: {{ $x }}%; --y: {{ $y }}%; --s: {{ $size }}"></span>
        @endforeach
    </div>

    <div class="story-sky story-sky--deep" data-layer="deep"></div>

    <div class="story-sky story-sky--starry" data-layer="starry">
        <svg class="story-vg__wind" viewBox="0 0 400 800" preserveAspectRatio="xMidYMid slice">
            <path class="is-pale" d="M-20 150 C 50 128, 110 182, 180 160 S 320 108, 420 140" />
            <path class="is-mid" d="M-20 252 C 60 212, 120 302, 200 264 S 330 190, 420 236" />
            <path class="is-pale" d="M-20 292 C 70 252, 140 342, 220 302 S 350 238, 420 276" />
            <path class="is-deep" d="M-20 384 C 80 352, 150 424, 240 392 S 360 330, 420 362" />
            <path class="is-mid" d="M-20 440 C 90 420, 170 470, 260 444 S 370 404, 420 424" />
        </svg>

        <svg class="story-vg__vortex story-vg__vortex--big" viewBox="0 0 200 200">
            @foreach($strands as $strand)
                <path class="{{ $strand['class'] }}" d="{{ $spiral($strand['start'], $strand['spread']) }}" />
            @endforeach
        </svg>
        <svg class="story-vg__vortex story-vg__vortex--small" viewBox="0 0 200 200">
            @foreach(array_slice($strands, 0, 2) as $strand)
                <path class="{{ $strand['class'] }}" d="{{ $spiral($strand['start'] + 2, $strand['spread']) }}" />
            @endforeach
        </svg>

        @foreach($stars as [$x, $y, $size, $delay])
            <span class="story-star" style="--x: {{ $x }}%; --y: {{ $y }}%; --s: {{ $size }}; --d: {{ $delay }}s">
                <svg viewBox="0 0 40 40">
                    <circle class="story-star__halo" cx="20" cy="20" r="16" />
                    <circle class="story-star__ring" cx="20" cy="20" r="10" />
                    <circle class="story-star__core" cx="20" cy="20" r="4.5" />
                </svg>
            </span>
        @endforeach

        <svg class="story-vg__land" viewBox="0 0 400 800" preserveAspectRatio="xMidYMax slice">
            <path class="story-vg__hills" d="M0 690 C 80 660, 160 684, 240 660 S 360 648, 400 668 L400 800 L0 800 Z" />
            <path class="story-vg__cypress" d="M58 800 C 36 716, 70 650, 56 574 C 48 514, 80 470, 72 410 C 68 364, 90 330, 86 282 C 100 330, 110 382, 104 434 C 116 486, 106 546, 118 606 C 126 684, 108 744, 124 800 Z" />
        </svg>
    </div>

    <div class="story-moon" data-layer="moon">
        <span class="story-moon__halo"></span>
        <span class="story-moon__disc"></span>
    </div>

    <div class="story-water" data-layer="water">
        <div class="story-glade">
            @foreach($glade as $index => $width)
                <i style="--i: {{ $index }}; --w: {{ $width }}%"></i>
            @endforeach
        </div>
    </div>

    <span class="story-shooting-star" data-shooting-star></span>
</div>
