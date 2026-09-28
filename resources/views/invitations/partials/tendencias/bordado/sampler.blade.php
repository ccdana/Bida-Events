{{--
    Itinerario de «Bordado a mano»: el día como un muestrario de puntadas. Cada momento es una hilera
    del muestrario, con su hora bordada en un parchecito y, debajo, una franja con una puntada distinta
    (pespunte, punto cruz, zigzag, cadeneta), que se borda al aparecer. Recibe $data (módulo itinerario).
--}}
@php
    $eventos = $data['eventos'] ?? [];
@endphp

<section class="inv-section reveal bd-sampler-section" id="itinerario">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['itinerary_eyebrow'] ?? 'Así será mi día',
            'title' => $data['titulo'] ?? 'Itinerario',
        ])

        @if(count($eventos) > 0)
            <ol class="bd-sampler">
                @foreach($eventos as $index => $evento)
                    <li class="bd-row bd-row--{{ $index % 4 }}" data-step style="--step: {{ $index }}">
                        <time class="bd-row__time">{{ $evento['hora'] ?? '' }}</time>
                        <div class="bd-row__body">
                            <h3 class="bd-row__title">{{ $evento['titulo'] ?? '' }}</h3>
                            @if(!empty($evento['descripcion']))
                                <p class="bd-row__text">{{ $evento['descripcion'] }}</p>
                            @endif
                        </div>
                        @if(! $loop->last)
                            <span class="bd-row__stitch" aria-hidden="true"></span>
                        @endif
                    </li>
                @endforeach
            </ol>
        @else
            <p class="inv-empty">{{ $invCopy['itinerary_empty'] ?? 'Muy pronto compartiremos el orden de la celebración.' }}</p>
        @endif
    </div>
</section>
