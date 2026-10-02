{{--
    Itinerario de «Álbum de stickers»: la lista de la colección. Una línea punteada impresa recorre la
    página y en ella, en orden, cada momento es un sticker chico pegado en su casilla: la hora en la cara
    y su número de colección en la franja. El título y la descripción van impresos al lado (en pantallas
    anchas la línea va al centro y los textos se turnan a cada lado). Los colores se turnan entre los
    de la paleta. Recibe $data (módulo itinerario).
--}}
@php
    $eventos = $data['eventos'] ?? [];
    $numberLabel = $invCopy['sticker_number'] ?? 'N.º';
@endphp

<section class="inv-section reveal st-schedule-section" id="itinerario">
    <div class="inv-wrap inv-wrap--wide">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['itinerary_eyebrow'] ?? 'Así será la fiesta',
            'title' => $data['titulo'] ?? 'Itinerario',
        ])

        @if(count($eventos) > 0)
            <ol class="st-list">
                @foreach($eventos as $index => $evento)
                    <li class="st-list__row" data-step style="--step: {{ $index }}">
                        <span class="st-list__slot">
                            <span class="st-fig st-fig--chip" data-sticker>
                                <span class="st-fig__face">
                                    <b class="st-chip__time">{{ $evento['hora'] ?? '' }}</b>
                                </span>
                                <span class="st-fig__band st-chip__no">{{ $numberLabel }} {{ $index + 1 }}</span>
                            </span>
                        </span>
                        <div class="st-list__text">
                            <h3 class="st-list__title">{{ $evento['titulo'] ?? '' }}</h3>
                            @if(!empty($evento['descripcion']))
                                <p class="st-list__caption">{{ $evento['descripcion'] }}</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        @else
            <p class="inv-empty">{{ $invCopy['itinerary_empty'] ?? 'Muy pronto compartiré el programa de la fiesta.' }}</p>
        @endif
    </div>
</section>
