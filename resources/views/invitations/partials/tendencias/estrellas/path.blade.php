{{--
    Itinerario de «Mapa de estrellas»: el día como una constelación que baja por la página. Cada
    momento es una estrella con su hora; las estrellas se unen con una línea de luz que se dibuja al
    aparecer la sección. Recibe $data (módulo itinerario).
--}}
@php
    $eventos = $data['eventos'] ?? [];
@endphp

<section class="inv-section reveal es-path-section" id="itinerario">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['itinerary_eyebrow'] ?? 'Mi día, estrella por estrella',
            'title' => $data['titulo'] ?? 'Itinerario',
        ])

        @if(count($eventos) > 0)
            <ol class="es-path">
                @foreach($eventos as $index => $evento)
                    <li class="es-point" data-step style="--step: {{ $index }}">
                        <span class="es-point__star" aria-hidden="true"></span>
                        <time class="es-point__time">{{ $evento['hora'] ?? '' }}</time>
                        <div class="es-point__body">
                            <h3 class="es-point__title">{{ $evento['titulo'] ?? '' }}</h3>
                            @if(!empty($evento['descripcion']))
                                <p class="es-point__text">{{ $evento['descripcion'] }}</p>
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
