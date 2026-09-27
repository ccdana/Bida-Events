{{--
    Itinerario de «Galería Quince»: el programa de la inauguración como el recorrido de un museo.
    Cada momento es una parada de la audioguía (01, 02, 03…) con su cédula —la hora, qué pasa y el
    detalle— y entre parada y parada, la flecha de señalética que indica por dónde seguir. Recibe $data (módulo itinerario).
--}}
@php
    $eventos = $data['eventos'] ?? [];
@endphp

<section class="inv-section reveal gq-program" id="itinerario">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['itinerary_eyebrow'] ?? 'Programa de la inauguración',
            'title' => $data['titulo'] ?? 'Itinerario',
        ])

        @if(count($eventos) > 0)
            <ol class="gq-route">
                @foreach($eventos as $index => $evento)
                    <li class="gq-stop" data-step style="--step: {{ $index }}">
                        <span class="gq-stop__room" aria-hidden="true">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                        <div class="gq-stop__card">
                            @if(!empty($evento['hora']))
                                <time class="gq-stop__time">{{ $evento['hora'] }}</time>
                            @endif
                            <h3 class="gq-stop__title">{{ $evento['titulo'] ?? '' }}</h3>
                            @if(!empty($evento['descripcion']))
                                <p class="gq-stop__text">{{ $evento['descripcion'] }}</p>
                            @endif
                        </div>
                        @unless($loop->last)
                            <span class="gq-stop__arrow" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4v16M6 14l6 6 6-6"/></svg>
                            </span>
                        @endunless
                    </li>
                @endforeach
            </ol>
        @else
            <p class="inv-empty">{{ $invCopy['itinerary_empty'] ?? 'Muy pronto publicaremos el programa de la inauguración.' }}</p>
        @endif
    </div>
</section>
