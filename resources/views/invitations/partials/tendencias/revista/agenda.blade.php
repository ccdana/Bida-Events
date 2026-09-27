{{--
    Itinerario de «Edición especial»: la página de agenda de la revista. Cada momento es una entrada
    de la cartelera: la hora en negrita, el nombre en cursiva y el detalle debajo, separados por
    filetes finos; el primero lleva el recuadro de «Imperdible». En pantallas anchas, a dos columnas.
    Recibe $data (módulo itinerario).
--}}
@php
    $eventos = $data['eventos'] ?? [];
@endphp

<section class="inv-section reveal rv-agenda-section" id="itinerario">
    <div class="inv-wrap inv-wrap--wide">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['itinerary_eyebrow'] ?? 'Agenda',
            'title' => $data['titulo'] ?? 'Itinerario',
        ])

        @if(count($eventos) > 0)
            <ol class="rv-agenda">
                @foreach($eventos as $index => $evento)
                    <li @class(['rv-listing', 'is-pick' => $index === 0]) data-step style="--step: {{ $index }}">
                        @if($index === 0)
                            <span class="rv-listing__pick">{{ $invCopy['mag_pick'] ?? 'Imperdible' }}</span>
                        @endif
                        @if(!empty($evento['hora']))
                            <time class="rv-listing__time">{{ $evento['hora'] }}</time>
                        @endif
                        <h3 class="rv-listing__title">{{ $evento['titulo'] ?? '' }}</h3>
                        @if(!empty($evento['descripcion']))
                            <p class="rv-listing__text">{{ $evento['descripcion'] }}</p>
                        @endif
                    </li>
                @endforeach
            </ol>
        @else
            <p class="inv-empty">{{ $invCopy['itinerary_empty'] ?? 'Muy pronto publicaremos la agenda del día.' }}</p>
        @endif
    </div>
</section>
