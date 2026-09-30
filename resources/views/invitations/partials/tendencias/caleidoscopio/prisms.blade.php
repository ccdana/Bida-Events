{{--
    Itinerario de «Caleidoscopio»: cada momento, un color. Una línea de luz baja por el centro y cada
    momento cuelga de ella con su prisma (que va pasando por los tres colores de la paleta), la hora y
    lo que pasa, en una celda de cristal. Recibe $data (módulo itinerario).
--}}
@php
    $eventos = $data['eventos'] ?? [];
@endphp

<section class="inv-section reveal ka-prisms-section" id="itinerario">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['itinerary_eyebrow'] ?? 'Cada momento, un color',
            'title' => $data['titulo'] ?? 'Itinerario',
        ])

        @if(count($eventos) > 0)
            <ol class="ka-prisms">
                @foreach($eventos as $index => $evento)
                    <li class="ka-prism" data-step style="--step: {{ $index }}">
                        <span class="ka-prism__gem" aria-hidden="true"></span>
                        <time class="ka-prism__time">{{ $evento['hora'] ?? '' }}</time>
                        <h3 class="ka-prism__title">{{ $evento['titulo'] ?? '' }}</h3>
                        @if(!empty($evento['descripcion']))
                            <p class="ka-prism__text">{{ $evento['descripcion'] }}</p>
                        @endif
                    </li>
                @endforeach
            </ol>
        @else
            <p class="inv-empty">{{ $invCopy['itinerary_empty'] ?? 'Muy pronto compartiré el programa de la noche.' }}</p>
        @endif
    </div>
</section>
