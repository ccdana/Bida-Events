{{--
    Itinerario de «Móvil de cuna»: cada momento cuelga del hilo que baja por la invitación. Es una
    tarjetita de fieltro cosida con la hora bordada en un círculo; las tarjetas se turnan a los lados
    del hilo y se mecen apenas. Recibe $data (módulo itinerario).
--}}
@php
    $eventos = $data['eventos'] ?? [];
    $felts = ['mv-felt--1', 'mv-felt--2', 'mv-felt--3'];
@endphp

<section class="inv-section reveal mv-steps-section" id="itinerario">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['itinerary_eyebrow'] ?? 'Así será mi día',
            'title' => $data['titulo'] ?? 'Itinerario',
        ])

        @if(count($eventos) > 0)
            <ol class="mv-steps">
                @foreach($eventos as $index => $evento)
                    <li class="mv-step {{ $index % 2 === 0 ? 'is-left' : 'is-right' }}" data-step style="--step: {{ $index }}; --t: {{ 4.6 + ($index % 3) * 0.7 }}s">
                        <div class="mv-step__tag mv-card">
                            @if(!empty($evento['hora']))
                                <time class="mv-step__time {{ $felts[$index % 3] }}">{{ $evento['hora'] }}</time>
                            @endif
                            <h3 class="mv-step__title">{{ $evento['titulo'] ?? '' }}</h3>
                            @if(!empty($evento['descripcion']))
                                <p class="mv-step__text">{{ $evento['descripcion'] }}</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        @else
            <p class="inv-empty">{{ $invCopy['itinerary_empty'] ?? 'Muy pronto compartiremos el orden de la celebración.' }}</p>
        @endif
    </div>
</section>
