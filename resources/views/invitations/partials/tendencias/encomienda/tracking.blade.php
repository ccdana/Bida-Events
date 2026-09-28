{{--
    Itinerario de «Encomienda especial»: el seguimiento del envío. Cada momento es un control del
    recorrido: la hora, lo que pasa y su estado (en preparación, en camino, entregado), unidos por la
    ruta punteada; al aparecer, una cajita baja por la ruta de control en control y cada estado se
    marca como con un sello. Recibe $data (módulo itinerario).
--}}
@php
    $eventos = $data['eventos'] ?? [];
    $lastIndex = count($eventos) - 1;
@endphp

<section class="inv-section reveal en-track-section" id="itinerario">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['itinerary_eyebrow'] ?? 'Seguimiento del envío',
            'title' => $data['titulo'] ?? 'Itinerario',
        ])

        @if(count($eventos) > 0)
            <ol class="en-track" style="--steps: {{ count($eventos) }}">
                <li class="en-track__parcel" aria-hidden="true"><i></i></li>
                @foreach($eventos as $index => $evento)
                    @php($status = $index === 0 ? 'En preparación' : ($index === $lastIndex ? 'Entregado' : 'En camino'))
                    <li @class(['en-step', 'is-first' => $index === 0, 'is-last' => $index === $lastIndex]) data-step style="--step: {{ $index }}">
                        <span class="en-step__dot" aria-hidden="true"></span>
                        <time class="en-step__time">{{ $evento['hora'] ?? '' }}</time>
                        <div class="en-step__body">
                            <h3 class="en-step__title">{{ $evento['titulo'] ?? '' }}</h3>
                            @if(!empty($evento['descripcion']))
                                <p class="en-step__text">{{ $evento['descripcion'] }}</p>
                            @endif
                            <span class="en-step__status">{{ $status }}</span>
                        </div>
                    </li>
                @endforeach
            </ol>
        @else
            <p class="inv-empty">{{ $invCopy['itinerary_empty'] ?? 'Muy pronto compartiremos el programa.' }}</p>
        @endif
    </div>
</section>
