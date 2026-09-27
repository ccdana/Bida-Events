{{--
    Portada de «Birrete al aire»: la medalla de la promoción. La foto (o las iniciales) va en un sello
    redondo con doble aro, la carrera escrita por el borde de arriba y dos ramas de laurel abrazándolo;
    una cinta cruza abajo con la promoción y el año, y encima se apoya un birrete con la borla ya del
    lado izquierdo. Detrás, birretes que siguen dando vueltas en el aire. Estilos en themes/birrete.css.
--}}
@php
    $heroEyebrow = $invCopy['hero_eyebrow'] ?? 'Me gradúo';
    $career = trim((string) ($page->welcome['subtitulo'] ?? ''));
    $heroMessage = $page->welcome['mensaje'] ?? '';
    $heroDay = ($page->welcome['fecha_texto'] ?? null)
        ?: \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('l j \d\e F'));
    $classLine = ($invCopy['hero_class_label'] ?? 'Promoción').' '.$page->eventDate->format('Y');

    // La carrera va por el borde de arriba del sello; la letra se achica para que entre en el arco
    $ringText = mb_strtoupper($career !== '' ? $career : $classLine);
    $ringSize = min(13, round(290 / max(1, mb_strlen($ringText) * 0.95), 1));
    $ringId = 'br-ring-'.\Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(5));

    // Ramas de laurel: pares de hojas a lo largo de cada rama, más chicas hacia la punta
    $laurel = [];
    foreach (['left' => [96, 1], 'right' => [84, -1]] as $branch => [$start, $direction]) {
        for ($leaf = 0; $leaf < 12; $leaf++) {
            $angle = $start + $direction * $leaf * 8;
            $x = 160 + 138 * cos(deg2rad($angle));
            $y = 160 + 138 * sin(deg2rad($angle));
            $tangent = $angle + 90 * $direction;
            foreach ([36, -36] as $side => $spread) {
                $laurel[] = [$branch, $x, $y, $tangent + $spread, 1 - $leaf * 0.035, $side];
            }
        }
    }
@endphp

<header id="inicio" class="inv-hero br-hero">
    <div class="br-hero__sky" aria-hidden="true">
        @foreach([1, 2, 3, 4] as $toss)
            <span class="br-hero__toss br-hero__toss--{{ $toss }}">
                @include('invitations.partials.birrete.cap', ['side' => $toss % 2 ? 'right' : 'left'])
            </span>
        @endforeach
    </div>

    <p class="br-kicker inv-fade-up">{{ $heroEyebrow }}</p>

    <div class="br-seal inv-fade-up inv-fade-up--1">
        <svg class="br-seal__art" viewBox="0 0 320 320" aria-hidden="true" focusable="false">
            <defs>
                <path id="{{ $ringId }}" d="M44 105.9 A128 128 0 0 1 276 105.9"/>
            </defs>
            <circle class="br-seal__ring" cx="160" cy="160" r="114"/>
            <circle class="br-seal__ring br-seal__ring--thin" cx="160" cy="160" r="121"/>
            <text class="br-seal__text" style="font-size: {{ $ringSize }}px">
                <textPath href="#{{ $ringId }}" startOffset="50%" text-anchor="middle">{{ $ringText }}</textPath>
            </text>
            @foreach(['left' => 'M160 298 A138 138 0 0 1 25 131.3', 'right' => 'M160 298 A138 138 0 0 0 295 131.3'] as $branch => $stem)
                <path class="br-laurel__stem br-laurel__stem--{{ $branch }}" d="{{ $stem }}"/>
            @endforeach
            @foreach($laurel as $index => [$branch, $x, $y, $rotation, $scale, $side])
                <path class="br-laurel__leaf br-laurel__leaf--{{ $side ? 'in' : 'out' }}" style="--i: {{ intdiv($index % 24, 2) }}"
                    transform="{{ sprintf('translate(%.1f %.1f) rotate(%.1f) scale(%.2f)', $x, $y, $rotation, $scale) }}"
                    d="M0 0 C6 -7 18 -7 26 0 C18 7 6 7 0 0 Z"/>
            @endforeach
        </svg>

        <div class="br-seal__photo">
            @include('invitations.partials.tendencias.photo', ['widths' => [480, 768], 'width' => 768, 'sizes' => '(min-width: 640px) 15rem, 56vw'])
        </div>

        <p class="br-seal__banner"><span>{{ $classLine }}</span></p>

        @include('invitations.partials.birrete.cap', ['side' => 'left', 'class' => 'br-seal__cap'])
    </div>

    <h1 class="br-name inv-fade-up inv-fade-up--2">{{ $page->displayName }}</h1>

    @if($career !== '')
        <p class="br-career inv-fade-up inv-fade-up--2">{{ $career }}</p>
    @endif

    <dl class="br-facts inv-fade-up inv-fade-up--3">
        <div>
            <dt>{{ $invCopy['hero_day_label'] ?? 'Día' }}</dt>
            <dd>{{ $heroDay }}</dd>
        </div>
        <div>
            <dt>{{ $invCopy['hero_time_label'] ?? 'Hora' }}</dt>
            <dd>{{ $page->eventDate->format('H:i') }}</dd>
        </div>
        @if($page->placeName)
            <div class="br-facts__place">
                <dt>{{ $invCopy['hero_place_label'] ?? 'Lugar' }}</dt>
                <dd>{{ $page->placeName }}</dd>
            </div>
        @endif
    </dl>

    @if(!empty($heroMessage))
        <p class="br-message inv-fade-up inv-fade-up--3">{{ $heroMessage }}</p>
    @endif

    <a href="#contenido" class="inv-hero__scroll br-hero__scroll">
        {{ $invCopy['scroll_hint'] ?? 'Desliza' }}
        <span class="inv-hero__scroll-line" aria-hidden="true"></span>
    </a>
</header>
