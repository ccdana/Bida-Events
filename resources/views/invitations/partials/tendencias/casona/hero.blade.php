{{--
    Portada de «Casa de muñecas de medianoche»: la casa ya abierta, vista por dentro y simétrica. Arriba,
    su nombre bajo la luna y el tejado con la buhardilla y el gato. En el piso de arriba, el salón: el
    retrato (la foto, en un marco de arco) entre dos apliques con su vela, y el fantasma que se asoma
    detrás del marco. En el de abajo, a un lado el calendario de pared con el día, al otro el reloj de
    pie con las agujas en la hora de la fiesta, y al medio la puerta. Abajo, el letrero del jardín con
    el lugar y el mensaje. Al abrirse la casa, se asienta, los cuartos se encienden uno por uno y cada
    pieza llega a su lugar. Estilos en css/invitation/tendencias/casona.css.
--}}
@php
    $heroEyebrow = ($page->welcome['subtitulo'] ?? null) ?: ($invCopy['hero_eyebrow'] ?? 'Fiesta de Halloween');
    $heroMessage = $page->welcome['mensaje'] ?? '';
    $date = $page->eventDate->locale('es');
    // Las agujas del reloj de pie marcan la hora de la fiesta
    $clockHour = (($date->hour % 12) + $date->minute / 60) * 30;
    $clockMinute = $date->minute * 6;
    // Los cuartos de la portada: la palabra de la placa de cada sección (CSS counter)
    $roomLabel = trim(str_replace(['<', '>', '"', '\\'], '', (string) ($invCopy['house_room'] ?? 'Cuarto')));
@endphp

<style>
    .inv-casona {
        --cs-room-label: "{{ $roomLabel }}";
    }
</style>

<header id="inicio" class="inv-hero cs-hero">
    <p class="cs-kicker">{{ $heroEyebrow }}</p>
    <h1 class="cs-name">{{ $page->displayName }}</h1>

    <div class="cs-house">
        <div class="cs-house__roof">
            <span class="cs-house__moon" aria-hidden="true"></span>
            @include('invitations.partials.tendencias.casona.roof', ['id' => 'cs-hero-roof'])
            @include('invitations.partials.tendencias.casona.bat', ['class' => 'cs-house__bat cs-house__bat--left'])
            @include('invitations.partials.tendencias.casona.bat', ['class' => 'cs-house__bat cs-house__bat--right'])
        </div>

        <div class="cs-house__body">
            {{-- Arriba, el salón con el retrato --}}
            <div class="cs-room cs-room--hall" style="--n: 2">
                <span class="cs-room__light" aria-hidden="true"></span>
                <span class="cs-sconce cs-sconce--left" aria-hidden="true"><i></i></span>
                <figure class="cs-frame">
                    <span class="cs-frame__photo">
                        @include('invitations.partials.tendencias.photo', ['widths' => [480, 768], 'width' => 768, 'sizes' => '(min-width: 640px) 11rem, 38vw'])
                    </span>
                </figure>
                <span class="cs-sconce cs-sconce--right" aria-hidden="true"><i></i></span>
                @include('invitations.partials.tendencias.casona.ghost', ['class' => 'cs-hall__ghost'])
            </div>

            {{-- Abajo: el calendario, la puerta y el reloj de pie --}}
            <div class="cs-house__lower">
                <div class="cs-room cs-room--side" style="--n: 0">
                    <span class="cs-room__light" aria-hidden="true"></span>
                    <p class="cs-calendar">
                        <span class="cs-calendar__label">{{ $invCopy['house_date'] ?? 'Fecha' }}</span>
                        <span class="cs-calendar__weekday">{{ \Illuminate\Support\Str::ucfirst($date->translatedFormat('l')) }}</span>
                        <strong class="cs-calendar__day">{{ $date->format('j') }}</strong>
                        <span class="cs-calendar__month">{{ $date->translatedFormat('F') }}</span>
                    </p>
                </div>

                <div class="cs-entry" aria-hidden="true">
                    <span class="cs-entry__transom"></span>
                    <span class="cs-entry__door"><i class="cs-entry__knob"></i></span>
                </div>

                <div class="cs-room cs-room--side" style="--n: 1">
                    <span class="cs-room__light" aria-hidden="true"></span>
                    <div class="cs-clock" aria-hidden="true">
                        <span class="cs-clock__crown"></span>
                        <span class="cs-clock__face">
                            <i class="cs-clock__hand cs-clock__hand--hour" style="--a: {{ round($clockHour, 1) }}deg"></i>
                            <i class="cs-clock__hand cs-clock__hand--minute" style="--a: {{ $clockMinute }}deg"></i>
                        </span>
                        <span class="cs-clock__case"><i class="cs-clock__pendulum"></i></span>
                    </div>
                    <p class="cs-clock__time">
                        <span>{{ $invCopy['house_time'] ?? 'Hora' }}</span>
                        <strong>{{ $date->format('H:i') }}</strong>
                    </p>
                </div>
            </div>
        </div>

        <span class="cs-house__base" aria-hidden="true"><i class="cs-house__steps"></i></span>
    </div>

    {{-- El letrero del jardín: dónde queda la casa --}}
    @if($page->placeName)
        <p class="cs-signpost">
            <span class="cs-signpost__board">
                <small>{{ $invCopy['house_place'] ?? 'La casa queda en' }}</small>
                {{ $page->placeName }}
            </span>
        </p>
    @endif

    @if(!empty($heroMessage))
        <p class="cs-message">{{ $heroMessage }}</p>
    @endif

    <a href="#contenido" class="inv-hero__scroll cs-hero__scroll">
        {{ $invCopy['scroll_hint'] ?? 'Desliza' }}
        <span class="inv-hero__scroll-line" aria-hidden="true"></span>
    </a>
</header>
