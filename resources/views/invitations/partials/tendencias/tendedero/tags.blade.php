{{--
    Itinerario de «Tendedero»: cada momento es una etiqueta de papel colgada con su pinza en un cordel
    que sigue de largo: se desliza de lado para ver las demás, como quien recorre el tendedero. La hora
    va arriba, en grande. Recibe $data (módulo itinerario).
--}}
@php
    $eventos = $data['eventos'] ?? [];
@endphp

<section class="inv-section reveal td-tags-section" id="itinerario">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['itinerary_eyebrow'] ?? 'Así será la celebración',
            'title' => $data['titulo'] ?? 'Itinerario',
        ])
    </div>

    @if(count($eventos) > 0)
        <div class="td-string">
            <span class="td-line" aria-hidden="true"></span>
            <ol class="td-string__list">
                @foreach($eventos as $index => $evento)
                    <li class="td-moment" data-step style="--step: {{ $index }}; --t: {{ 4 + ($index % 3) * 0.7 }}s">
                        <span class="td-pin" aria-hidden="true"></span>
                        @if(!empty($evento['hora']))
                            <time class="td-moment__time">{{ $evento['hora'] }}</time>
                        @endif
                        <h3 class="td-moment__title">{{ $evento['titulo'] ?? '' }}</h3>
                        @if(!empty($evento['descripcion']))
                            <p class="td-moment__text">{{ $evento['descripcion'] }}</p>
                        @endif
                    </li>
                @endforeach
            </ol>
        </div>
    @else
        <div class="inv-wrap">
            <p class="inv-empty">{{ $invCopy['itinerary_empty'] ?? 'Muy pronto compartiremos el programa.' }}</p>
        </div>
    @endif
</section>
