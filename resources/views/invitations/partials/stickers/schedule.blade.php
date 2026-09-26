{{--
    Itinerario de «Álbum de stickers»: la página del álbum con sus casillas numeradas en orden. Cada
    momento es un sticker con la hora como número y el título en la franja; la descripción va impresa
    debajo, en la página. Los colores se turnan entre los tres de la paleta.
    Recibe $data (módulo itinerario).
--}}
@php
    $eventos = $data['eventos'] ?? [];
@endphp

<section class="inv-section reveal st-schedule-section" id="itinerario">
    <div class="inv-wrap inv-wrap--wide">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['itinerary_eyebrow'] ?? 'Así será la fiesta',
            'title' => $data['titulo'] ?? 'Itinerario',
        ])

        @if(count($eventos) > 0)
            <ol class="st-album">
                @foreach($eventos as $index => $evento)
                    <li class="st-album__slot" data-step style="--step: {{ $index }}">
                        <span class="st-album__no" aria-hidden="true">{{ $index + 1 }}</span>
                        <div class="st-slot">
                            <article class="st-fig st-fig--mini st-item" data-sticker>
                                <div class="st-fig__face">
                                    <b class="st-fig__big st-fig__big--time">{{ $evento['hora'] ?? '' }}</b>
                                </div>
                                <h3 class="st-fig__band">{{ $evento['titulo'] ?? '' }}</h3>
                            </article>
                        </div>
                        @if(!empty($evento['descripcion']))
                            <p class="st-album__caption">{{ $evento['descripcion'] }}</p>
                        @endif
                    </li>
                @endforeach
            </ol>
        @else
            <p class="inv-empty">{{ $invCopy['itinerary_empty'] ?? 'Muy pronto compartiré el programa de la fiesta.' }}</p>
        @endif
    </div>
</section>
