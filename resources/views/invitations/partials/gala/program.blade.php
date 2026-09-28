{{--
    Itinerario de «Noche de gala»: el programa de la noche. Cada momento es un camafeo ovalado con su
    hora, enhebrado en un hilo de perlas que baja por la izquierda; al lado, lo que pasa. Al final,
    un colgante. Recibe $data (módulo itinerario).
--}}
@php
    $eventos = $data['eventos'] ?? [];
@endphp

<section class="inv-section reveal ga-program-section" id="itinerario">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['itinerary_eyebrow'] ?? 'Así será mi noche',
            'title' => $data['titulo'] ?? 'Itinerario',
        ])

        @if(count($eventos) > 0)
            <ol class="ga-program">
                @foreach($eventos as $index => $evento)
                    <li class="ga-moment" data-step style="--step: {{ $index }}">
                        <span class="ga-moment__cameo">
                            <time>{{ $evento['hora'] ?? '' }}</time>
                        </span>
                        <div class="ga-moment__body">
                            <h3 class="ga-moment__title">{{ $evento['titulo'] ?? '' }}</h3>
                            @if(!empty($evento['descripcion']))
                                <p class="ga-moment__text">{{ $evento['descripcion'] }}</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
            <span class="ga-program__pendant" aria-hidden="true"></span>
        @else
            <p class="inv-empty">{{ $invCopy['itinerary_empty'] ?? 'Muy pronto compartiré el programa de la noche.' }}</p>
        @endif
    </div>
</section>
