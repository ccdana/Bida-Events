{{--
    Itinerario de «Función de medianoche»: la cartelera del cine. Cada momento es una función con su
    horario en letras de marquesina (cada número en su casilla iluminada), el título en mayúsculas y
    la sinopsis debajo. Recibe $data (módulo itinerario).
--}}
@php
    $eventos = $data['eventos'] ?? [];
@endphp

<section class="inv-section reveal fn-showtimes-section" id="itinerario">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['itinerary_eyebrow'] ?? 'Cartelera',
            'title' => $data['titulo'] ?? 'Itinerario',
        ])

        @if(count($eventos) > 0)
            <ol class="fn-board">
                @foreach($eventos as $index => $evento)
                    <li class="fn-show" data-step style="--step: {{ $index }}">
                        @if(!empty($evento['hora']))
                            <time class="fn-show__time">
                                <span class="tr-sr-only">{{ $evento['hora'] }}</span>
                                @foreach(mb_str_split((string) $evento['hora']) as $char)
                                    <span class="fn-show__char" aria-hidden="true">{{ $char }}</span>
                                @endforeach
                            </time>
                        @endif
                        <div class="fn-show__body">
                            <h3 class="fn-show__title">{{ $evento['titulo'] ?? '' }}</h3>
                            @if(!empty($evento['descripcion']))
                                <p class="fn-show__text">{{ $evento['descripcion'] }}</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        @else
            <p class="inv-empty">{{ $invCopy['itinerary_empty'] ?? 'Muy pronto publicaremos la cartelera de la noche.' }}</p>
        @endif
    </div>
</section>
