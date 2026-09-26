{{--
    Itinerario de «Dos caminos»: el día como un camino punteado con paradas. Cada parada es un aro
    con su hora y lo que pasa; la última es la llegada (aro lleno). Recibe $data (módulo itinerario).
--}}
@php
    $eventos = $data['eventos'] ?? [];
    $last = count($eventos) - 1;
@endphp

<section class="inv-section reveal dc-route-section" id="itinerario">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['itinerary_eyebrow'] ?? 'Las paradas del día',
            'title' => $data['titulo'] ?? 'Itinerario',
        ])

        @if(count($eventos) > 0)
            <ol class="dc-stops">
                @foreach($eventos as $index => $evento)
                    <li @class(['dc-stop', 'is-last' => $index === $last]) data-step style="--step: {{ $index }}">
                        <span class="dc-stop__ring" aria-hidden="true"></span>
                        @if(!empty($evento['hora']))
                            <time class="dc-stop__time">{{ $evento['hora'] }}</time>
                        @endif
                        <h3 class="dc-stop__title">{{ $evento['titulo'] ?? '' }}</h3>
                        @if(!empty($evento['descripcion']))
                            <p class="dc-stop__text">{{ $evento['descripcion'] }}</p>
                        @endif
                        @if($index === $last && $last > 0)
                            <p class="dc-stop__end">{{ $invCopy['route_end'] ?? 'Llegada' }}</p>
                        @endif
                    </li>
                @endforeach
            </ol>
        @else
            <p class="inv-empty">{{ $invCopy['itinerary_empty'] ?? 'Muy pronto marcaremos las paradas del día.' }}</p>
        @endif
    </div>
</section>
