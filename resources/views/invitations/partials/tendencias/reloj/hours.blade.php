{{--
    Itinerario de «A la misma hora»: hora por hora. Cada momento lleva un relojito con las agujas en su
    hora exacta (sale de la hora escrita en el itinerario), unidos por la cadena del reloj de bolsillo;
    al lado, la hora, lo que pasa y su descripción. Recibe $data (módulo itinerario).
--}}
@php
    $eventos = $data['eventos'] ?? [];
    // «20:45» → los ángulos de las agujas (hora y minutos)
    $handsFor = function (?string $hora): array {
        if (! preg_match('/(\d{1,2})[:.h](\d{2})/u', (string) $hora, $match)) {
            return [0, 0];
        }

        $minute = (int) $match[2];

        return [((int) $match[1] % 12) * 30 + $minute * 0.5, $minute * 6];
    };
@endphp

<section class="inv-section reveal rl-hours-section" id="itinerario">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['itinerary_eyebrow'] ?? 'Hora por hora',
            'title' => $data['titulo'] ?? 'Itinerario',
        ])

        @if(count($eventos) > 0)
            <ol class="rl-hours">
                @foreach($eventos as $index => $evento)
                    @php([$hourAngle, $minuteAngle] = $handsFor($evento['hora'] ?? null))
                    <li class="rl-moment" data-step style="--step: {{ $index }}">
                        <span class="rl-mini" style="--h: {{ $hourAngle }}deg; --m: {{ $minuteAngle }}deg" aria-hidden="true">
                            <i class="rl-mini__hour"></i>
                            <i class="rl-mini__minute"></i>
                        </span>
                        <div class="rl-moment__body">
                            <time class="rl-moment__time">{{ $evento['hora'] ?? '' }}</time>
                            <h3 class="rl-moment__title">{{ $evento['titulo'] ?? '' }}</h3>
                            @if(!empty($evento['descripcion']))
                                <p class="rl-moment__text">{{ $evento['descripcion'] }}</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        @else
            <p class="inv-empty">{{ $invCopy['itinerary_empty'] ?? 'Muy pronto compartiremos cada hora del día.' }}</p>
        @endif
    </div>
</section>
