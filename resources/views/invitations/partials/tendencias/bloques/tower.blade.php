{{--
    Itinerario de «Bloques de juguete»: el programa como una torre de bloques. Cada momento es un piso:
    un bloque chico con la hora y, al lado, la tabla ancha con lo que pasa; los pisos no quedan
    perfectamente alineados, como una torre armada a mano, y caen uno sobre otro al aparecer.
    Arriba de todo, un bloque con una estrella. Recibe $data (módulo itinerario).
--}}
@php
    $eventos = $data['eventos'] ?? [];
    $shifts = [0, 10, -8, 6, -12, 4];
@endphp

<section class="inv-section reveal bl-tower-section" id="itinerario">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['itinerary_eyebrow'] ?? 'Bloque por bloque',
            'title' => $data['titulo'] ?? 'Itinerario',
        ])

        @if(count($eventos) > 0)
            <ol class="bl-tower">
                <li class="bl-tower__top" aria-hidden="true">
                    @include('invitations.partials.tendencias.bloques.block', ['letter' => '', 'tone' => 2, 'class' => 'bl-tower__star'])
                </li>
                @foreach($eventos as $index => $evento)
                    <li class="bl-floor bl-tone--{{ $index % 3 }}" style="--step: {{ $index }}; --shift: {{ $shifts[$index % count($shifts)] }}px">
                        @include('invitations.partials.tendencias.bloques.block', ['letter' => $evento['hora'] ?? '', 'tone' => $index % 3, 'class' => 'bl-floor__time'])
                        <div class="bl-floor__body">
                            <h3 class="bl-floor__title">{{ $evento['titulo'] ?? '' }}</h3>
                            @if(!empty($evento['descripcion']))
                                <p class="bl-floor__text">{{ $evento['descripcion'] }}</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        @else
            <p class="inv-empty">{{ $invCopy['itinerary_empty'] ?? 'Muy pronto compartiremos el programa.' }}</p>
        @endif
    </div>
</section>
