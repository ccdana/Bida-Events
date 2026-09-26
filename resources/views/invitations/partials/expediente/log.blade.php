{{--
    Itinerario de «Expediente abierto»: la bitácora de la noche, escrita a máquina en una hoja con
    margen. Cada entrada tiene su hora y lo que pasa. Recibe $data (módulo itinerario).
--}}
@php
    $eventos = $data['eventos'] ?? [];
@endphp

<section class="inv-section reveal ex-log-section" id="itinerario">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['itinerary_eyebrow'] ?? 'Bitácora de la noche',
            'title' => $data['titulo'] ?? 'Itinerario',
        ])

        @if(count($eventos) > 0)
            <ol class="ex-log">
                @foreach($eventos as $index => $evento)
                    <li class="ex-log__entry" data-step style="--step: {{ $index }}">
                        <time class="ex-log__time">{{ $evento['hora'] ?? '' }}</time>
                        <div>
                            <h3 class="ex-log__title">{{ $evento['titulo'] ?? '' }}</h3>
                            @if(!empty($evento['descripcion']))
                                <p class="ex-log__text">{{ $evento['descripcion'] }}</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        @else
            <p class="inv-empty">{{ $invCopy['itinerary_empty'] ?? 'Muy pronto anotaremos lo que pasará esa noche.' }}</p>
        @endif
    </div>
</section>
