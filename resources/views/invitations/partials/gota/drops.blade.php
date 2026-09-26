{{--
    Itinerario de «La gota»: cada momento del día es una gota con su hora adentro, una debajo de la
    otra y unidas por una onda. Recibe $data (módulo itinerario).
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
                        <span class="gt-drop__shape">
                            <time class="gt-drop__time">{{ $evento['hora'] ?? '' }}</time>
                        </span>
                        <h3 class="gt-drop__title">{{ $evento['titulo'] ?? '' }}</h3>
                        @if(!empty($evento['descripcion']))
                            <p class="gt-drop__text">{{ $evento['descripcion'] }}</p>
                        @endif
                    </li>
                @endforeach
            </ol>
        @else
            <p class="inv-empty">{{ $invCopy['itinerary_empty'] ?? 'Muy pronto compartiremos el orden de la celebración.' }}</p>
        @endif
    </div>
</section>
