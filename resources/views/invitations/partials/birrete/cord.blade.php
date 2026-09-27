{{--
    Itinerario de «Birrete al aire»: el día es el cordón de la borla. Baja trenzado por la izquierda,
    cada momento es un nudo con su hora y al final cuelga la borla. Recibe $data (módulo itinerario).
--}}
@php
    $eventos = $data['eventos'] ?? [];
@endphp

<section class="inv-section reveal br-cord-section" id="itinerario">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['itinerary_eyebrow'] ?? 'Así será el día',
            'title' => $data['titulo'] ?? 'Itinerario',
        ])

        @if(count($eventos) > 0)
            <ol class="br-cord">
                @foreach($eventos as $index => $evento)
                    <li class="br-knot" data-step style="--step: {{ $index }}">
                        <span class="br-knot__tie" aria-hidden="true"></span>
                        <time class="br-knot__time">{{ $evento['hora'] ?? '' }}</time>
                        <div class="br-knot__body">
                            <h3 class="br-knot__title">{{ $evento['titulo'] ?? '' }}</h3>
                            @if(!empty($evento['descripcion']))
                                <p class="br-knot__text">{{ $evento['descripcion'] }}</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
            {{-- La borla al final del cordón --}}
            <span class="br-cord__tassel" aria-hidden="true"><i></i></span>
        @else
            <p class="inv-empty">{{ $invCopy['itinerary_empty'] ?? 'Muy pronto compartiré el horario del acto y de la fiesta.' }}</p>
        @endif
    </div>
</section>
