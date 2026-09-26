{{--
    Itinerario de «Carta de baile»: el programa de la noche como en una carta de baile. Cada momento
    es un renglón numerado (es una secuencia: entrada, vals, brindis…) con puntos guía hasta la hora.
    Recibe $data (módulo itinerario).
--}}
@php
    $eventos = $data['eventos'] ?? [];
@endphp

<section class="inv-section reveal cb-program" id="itinerario">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['itinerary_eyebrow'] ?? 'El programa de la noche',
            'title' => $data['titulo'] ?? 'Programa',
        ])

        @if(count($eventos) > 0)
            <ol class="cb-program__list">
                @foreach($eventos as $index => $evento)
                    <li class="cb-program__item" data-step style="--step: {{ $index }}">
                        <span class="cb-program__num" aria-hidden="true">{{ $index + 1 }}</span>
                        <div class="cb-program__body">
                            <div class="cb-program__line">
                                <h3 class="cb-program__title">{{ $evento['titulo'] ?? '' }}</h3>
                                @if(!empty($evento['hora']))
                                    <span class="cb-leader" aria-hidden="true"></span>
                                    <time class="cb-program__time">{{ $evento['hora'] }}</time>
                                @endif
                            </div>
                            @if(!empty($evento['descripcion']))
                                <p class="cb-program__text">{{ $evento['descripcion'] }}</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        @else
            <p class="inv-empty">{{ $invCopy['itinerary_empty'] ?? 'Muy pronto escribiremos el programa del baile.' }}</p>
        @endif
    </div>
</section>
