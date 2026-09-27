{{--
    Itinerario de «Entre nubes»: el día sube de nube en nube. Cada momento lleva su hora en una
    nubecita y se alterna de lado; un camino punteado va de una nube a la siguiente, como el recorrido
    de una cometa. Recibe $data (módulo itinerario).
--}}
@php
    $eventos = $data['eventos'] ?? [];
@endphp

<section class="inv-section reveal nb-steps-section" id="itinerario">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['itinerary_eyebrow'] ?? 'Así será mi día',
            'title' => $data['titulo'] ?? 'Itinerario',
        ])

        @if(count($eventos) > 0)
            <ol class="nb-steps">
                @foreach($eventos as $index => $evento)
                    <li class="nb-step" data-step style="--step: {{ $index }}">
                        <span class="nb-step__cloud">
                            @include('invitations.partials.nubes.cloud', ['class' => 'nb-step__shape', 'shade' => true])
                            <time class="nb-step__time">{{ $evento['hora'] ?? '' }}</time>
                        </span>
                        <div class="nb-step__body">
                            <h3 class="nb-step__title">{{ $evento['titulo'] ?? '' }}</h3>
                            @if(!empty($evento['descripcion']))
                                <p class="nb-step__text">{{ $evento['descripcion'] }}</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        @else
            <p class="inv-empty">{{ $invCopy['itinerary_empty'] ?? 'Muy pronto compartiremos el orden de la celebración.' }}</p>
        @endif
    </div>
</section>
