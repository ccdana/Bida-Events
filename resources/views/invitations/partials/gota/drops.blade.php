{{--
    Itinerario de «La gota»: cada momento es una gota que baja por el mismo hilo de agua con su hora
    adentro. Donde cae se abre una onda y al lado va lo que pasa; al final el agua queda quieta.
    Recibe $data (módulo itinerario).
--}}
@php
    $eventos = $data['eventos'] ?? [];
@endphp

<section class="inv-section reveal gt-drops-section" id="itinerario">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['itinerary_eyebrow'] ?? 'Así será mi día',
            'title' => $data['titulo'] ?? 'Itinerario',
        ])

        @if(count($eventos) > 0)
            <ol class="gt-drops">
                @foreach($eventos as $index => $evento)
                    <li class="gt-drop" data-step style="--step: {{ $index }}">
                        <span class="gt-drop__fall">
                            <span class="gt-drop__bead">
                                <time class="gt-drop__time">{{ $evento['hora'] ?? '' }}</time>
                            </span>
                            <span class="gt-drop__splash" aria-hidden="true"></span>
                        </span>
                        <div class="gt-drop__body">
                            <h3 class="gt-drop__title">{{ $evento['titulo'] ?? '' }}</h3>
                            @if(!empty($evento['descripcion']))
                                <p class="gt-drop__text">{{ $evento['descripcion'] }}</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
            <span class="gt-drops__pool" aria-hidden="true"><i></i><i></i><i></i></span>
        @else
            <p class="inv-empty">{{ $invCopy['itinerary_empty'] ?? 'Muy pronto compartiremos el orden de la celebración.' }}</p>
        @endif
    </div>
</section>
