{{--
    Itinerario de «Próxima salida»: el horario del día como un tablero de salidas, con la hora en la
    luz del panel y cada momento como destino. Es una tabla de verdad (hora y destino por fila).
    Recibe $data (módulo itinerario).
--}}
@php
    $eventos = $data['eventos'] ?? [];
@endphp

<section class="inv-section reveal ps-timetable-section" id="itinerario">
    <div class="inv-wrap inv-wrap--wide">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['itinerary_eyebrow'] ?? 'Horario del día',
            'title' => $data['titulo'] ?? 'Itinerario',
        ])

        @if(count($eventos) > 0)
            <table class="ps-timetable">
                <thead>
                    <tr>
                        <th scope="col">{{ $invCopy['board_time'] ?? 'Hora' }}</th>
                        <th scope="col">{{ $invCopy['board_destination'] ?? 'Destino' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($eventos as $index => $evento)
                        <tr data-step style="--step: {{ $index }}">
                            <td class="ps-timetable__time">{{ $evento['hora'] ?? '' }}</td>
                            <td>
                                <span class="ps-timetable__title">{{ $evento['titulo'] ?? '' }}</span>
                                @if(!empty($evento['descripcion']))
                                    <span class="ps-timetable__text">{{ $evento['descripcion'] }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="inv-empty">{{ $invCopy['itinerary_empty'] ?? 'Muy pronto publicaremos el horario del acto y de la fiesta.' }}</p>
        @endif
    </div>
</section>
