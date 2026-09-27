{{--
    Itinerario de «Gira mundial»: el line-up de la noche, como en los afiches de festival. Cada
    momento es un número del show con su hora de salida al escenario; los nombres van en mayúsculas,
    del más grande (el que abre) a los demás, separados por estrellitas. Recibe $data (módulo itinerario).
--}}
@php
    $eventos = $data['eventos'] ?? [];
@endphp

<section class="inv-section reveal gr-lineup-section" id="itinerario">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['itinerary_eyebrow'] ?? 'Line-up',
            'title' => $data['titulo'] ?? 'Itinerario',
        ])

        @if(count($eventos) > 0)
            <ol class="gr-lineup">
                @foreach($eventos as $index => $evento)
                    <li @class(['gr-act', 'is-headliner' => $index === 0]) data-step style="--step: {{ $index }}">
                        @if(!empty($evento['hora']))
                            <time class="gr-act__time">{{ $evento['hora'] }}</time>
                        @endif
                        <h3 class="gr-act__title">{{ $evento['titulo'] ?? '' }}</h3>
                        @if(!empty($evento['descripcion']))
                            <p class="gr-act__text">{{ $evento['descripcion'] }}</p>
                        @endif
                    </li>
                @endforeach
            </ol>
        @else
            <p class="inv-empty">{{ $invCopy['itinerary_empty'] ?? 'Muy pronto anunciaremos el line-up de la noche.' }}</p>
        @endif
    </div>
</section>
