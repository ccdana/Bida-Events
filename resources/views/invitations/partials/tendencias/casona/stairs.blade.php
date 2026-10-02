{{--
    Itinerario de «Casa de muñecas de medianoche»: la escalera de la casa. Cada momento es un escalón
    de madera, con el canto claro de la huella, que baja un poco más a la derecha que el anterior; en
    la contrahuella va la placa de latón con la hora y al lado, lo que pasa y su descripción. El
    fantasma acompaña desde arriba. Recibe $data (módulo itinerario).
--}}
@php
    $eventos = array_values($data['eventos'] ?? []);
@endphp

<section class="inv-section reveal cs-stairs-section" id="itinerario">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['itinerary_eyebrow'] ?? 'La escalera de la noche',
            'title' => $data['titulo'] ?? 'Itinerario',
        ])

        @if(count($eventos) > 0)
            <div class="cs-stairs-wrap">
                @include('invitations.partials.tendencias.casona.ghost', ['class' => 'cs-stairs__ghost'])
                <ol class="cs-stairs">
                    @foreach($eventos as $index => $evento)
                        <li class="cs-stair" data-step style="--step: {{ $index }}; --rise: {{ min($index, 4) }}">
                            <div class="cs-stair__plank">
                                <span class="cs-stair__plate">
                                    <small>{{ $invCopy['house_stair'] ?? 'Escalón' }} {{ $index + 1 }}</small>
                                    <time>{{ $evento['hora'] ?? '' }}</time>
                                </span>
                                <div class="cs-stair__body">
                                    <h3 class="cs-stair__title">{{ $evento['titulo'] ?? '' }}</h3>
                                    @if(!empty($evento['descripcion']))
                                        <p class="cs-stair__text">{{ $evento['descripcion'] }}</p>
                                    @endif
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>
        @else
            <p class="inv-empty">{{ $invCopy['itinerary_empty'] ?? 'Muy pronto publicaremos lo que pasará en cada piso.' }}</p>
        @endif
    </div>
</section>
