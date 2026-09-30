{{--
    Portada de «Día feriado»: el almanaque de pared, colgado de su clavo. Arriba la lámina con la foto;
    debajo, el bloque con la hoja del cumpleaños: la franja del mes, el número en rojo de feriado con el
    círculo de bolígrafo y la nota a mano, el día de la semana, el nombre, la edad, la hora, el lugar y
    la luna de ese día, y el mensaje como el pensamiento del día. Al pie, el mes entero con su día
    marcado (los días que ya pasaron quedan tachados). Estilos en css/invitation/tendencias/feriado.css.
--}}
@php
    $date = $page->eventDate->locale('es');
    $age = $page->age();
    $heroEyebrow = ($page->welcome['subtitulo'] ?? null) ?: ($invCopy['hero_eyebrow'] ?? '¡Celebremos juntos!');
    $heroMessage = trim((string) ($page->welcome['mensaje'] ?? ''));
    $monthName = \Illuminate\Support\Str::ucfirst($date->translatedFormat('F'));

    // El mes en grilla, de lunes a domingo, con los huecos del principio
    $firstDay = $date->copy()->startOfMonth();
    $monthCells = array_merge(array_fill(0, $firstDay->dayOfWeekIso - 1, null), range(1, $date->daysInMonth));
    $today = now($date->getTimezone());
    $passed = fn (int $day) => $firstDay->copy()->day($day)->endOfDay()->lt($today);

    // La luna de ese día, como la imprimen los almanaques: edad de la luna desde una luna nueva conocida
    $synodic = 29.530588853;
    $moonAge = fmod(($date->copy()->utc()->timestamp - 947182440) / 86400, $synodic);
    $moonAge = $moonAge < 0 ? $moonAge + $synodic : $moonAge;
    $moonName = match (true) {
        $moonAge < 1.85, $moonAge >= 27.68 => 'Luna nueva',
        $moonAge < 5.54, $moonAge >= 9.23 && $moonAge < 12.92 => 'Luna creciente',
        $moonAge < 9.23 => 'Cuarto creciente',
        $moonAge < 16.61 => 'Luna llena',
        $moonAge >= 20.3 && $moonAge < 23.99 => 'Cuarto menguante',
        default => 'Luna menguante',
    };
    // La parte iluminada: el borde de un lado y la línea de sombra, que es media elipse
    $moonAngle = $moonAge / $synodic * 2 * M_PI;
    $moonRx = round(abs(cos($moonAngle)) * 9, 2);
    $waxing = $moonAngle < M_PI;
    $gibbous = cos($moonAngle) < 0;
    $moonPath = sprintf('M12 3A9 9 0 0 %d 12 21A%s 9 0 0 %d 12 3Z', $waxing ? 1 : 0, $moonRx, $waxing === $gibbous ? 1 : 0);
@endphp

<header id="inicio" class="inv-hero fd-hero">
    <p class="fd-kicker inv-fade-up">{{ $heroEyebrow }}</p>

    <div class="fd-calendar inv-fade-up inv-fade-up--1">
        <span class="fd-calendar__nail" aria-hidden="true"></span>

        {{-- La lámina del almanaque: la foto --}}
        <figure class="fd-calendar__plate">
            @include('invitations.partials.tendencias.photo', ['widths' => [480, 768, 1200], 'width' => 768, 'sizes' => '(min-width: 640px) 22rem, 84vw'])
        </figure>

        {{-- La hoja del cumpleaños --}}
        <div class="fd-sheet">
            <p class="fd-sheet__band">
                <span>{{ $monthName }}</span>
                <span aria-hidden="true">·</span>
                <span>{{ $date->format('Y') }}</span>
            </p>

            <div class="fd-sheet__day">
                <span class="fd-sheet__number">
                    {{ $date->format('j') }}
                    <svg class="fd-circle" viewBox="0 0 200 150" preserveAspectRatio="none" aria-hidden="true" focusable="false">
                        <path d="M152 24C112 4 44 10 22 54C4 92 58 136 118 128C178 120 198 72 170 38C156 22 128 14 102 18"/>
                    </svg>
                </span>
                <span class="fd-sheet__note" aria-hidden="true">
                    {{ $invCopy['cal_note'] ?? '¡Mi cumple!' }}
                    <svg class="fd-arrow" viewBox="0 0 60 44" focusable="false"><path d="M50 4C34 4 18 14 10 34M10 34l-2-11M10 34l10-5"/></svg>
                </span>
            </div>
            <p class="fd-sheet__weekday">{{ \Illuminate\Support\Str::ucfirst($date->translatedFormat('l')) }}</p>

            <p class="fd-sheet__line">
                <span class="fd-sheet__holiday">{{ $invCopy['cal_holiday'] ?? 'Feriado' }}</span>
                {{ $invCopy['cal_line'] ?? 'Cumpleaños de' }}
            </p>
            <h1 class="fd-sheet__name">{{ $page->displayName }}</h1>
            @if($age !== null)
                <p class="fd-sheet__age">{{ $age }} {{ $invCopy['cal_age'] ?? 'años' }}</p>
            @endif

            <dl class="fd-sheet__facts">
                <div>
                    <dt>Hora</dt>
                    <dd>{{ $date->format('H:i') }}</dd>
                </div>
                @if($page->placeName)
                    <div>
                        <dt>Lugar</dt>
                        <dd><a href="#ubicacion">{{ $page->placeName }}</a></dd>
                    </div>
                @endif
                <div>
                    <dt>Luna</dt>
                    <dd class="fd-moon">
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <circle cx="12" cy="12" r="9"/>
                            <path d="{{ $moonPath }}"/>
                        </svg>
                        {{ $moonName }}
                    </dd>
                </div>
            </dl>

            @if($heroMessage !== '')
                <div class="fd-sheet__thought">
                    <p class="fd-sheet__thought-label">{{ $invCopy['cal_thought'] ?? 'Pensamiento del día' }}</p>
                    <p class="fd-sheet__thought-text">{{ $heroMessage }}</p>
                </div>
            @endif
        </div>
        <span class="fd-calendar__edges" aria-hidden="true"></span>

        {{-- El mes entero, con el día marcado --}}
        <div class="fd-month">
            <p class="fd-month__title">{{ $monthName }} {{ $date->format('Y') }}</p>
            <ol class="fd-month__grid" aria-label="{{ $monthName }} de {{ $date->format('Y') }}">
                @foreach(['L', 'M', 'M', 'J', 'V', 'S', 'D'] as $index => $initial)
                    <li @class(['fd-month__head', 'is-sunday' => $index === 6]) aria-hidden="true">{{ $initial }}</li>
                @endforeach
                @foreach($monthCells as $index => $day)
                    @if($day === null)
                        <li class="fd-month__blank" aria-hidden="true"></li>
                    @else
                        <li @class([
                            'fd-month__day',
                            'is-sunday' => $index % 7 === 6,
                            'is-passed' => $passed($day) && $day !== (int) $date->format('j'),
                            'is-marked' => $day === (int) $date->format('j'),
                        ]) @if($day === (int) $date->format('j')) aria-current="date" @endif>{{ $day }}</li>
                    @endif
                @endforeach
            </ol>
        </div>
    </div>

    <a href="#contenido" class="inv-hero__scroll fd-hero__scroll">
        {{ $invCopy['scroll_hint'] ?? 'Desliza' }}
        <span class="inv-hero__scroll-line" aria-hidden="true"></span>
    </a>
</header>
