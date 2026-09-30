{{--
    Itinerario de «Día feriado»: la agenda del día. Una hoja rayada con el margen rojo: a la izquierda
    la hora de cada momento con su ícono y, pasando el margen, lo que pasa y su descripción, un renglón
    por momento. Recibe $data (módulo itinerario).
--}}
@php
    $eventos = $data['eventos'] ?? [];
@endphp

<section class="inv-section reveal fd-agenda-section" id="itinerario">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['itinerary_eyebrow'] ?? 'La agenda del día',
            'title' => $data['titulo'] ?? 'Itinerario',
        ])

        @if(count($eventos) > 0)
            <ol class="fd-agenda">
                @foreach($eventos as $index => $evento)
                    <li class="fd-slot" data-step style="--step: {{ $index }}">
                        <span class="fd-slot__when">
                            <time class="fd-slot__time">{{ $evento['hora'] ?? '' }}</time>
                            @include('invitations.partials.itinerary-icon', ['name' => $evento['icono'] ?? null, 'class' => 'fd-slot__icon'])
                        </span>
                        <div class="fd-slot__body">
                            <h3 class="fd-slot__title">{{ $evento['titulo'] ?? '' }}</h3>
                            @if(!empty($evento['descripcion']))
                                <p class="fd-slot__text">{{ $evento['descripcion'] }}</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        @else
            <p class="inv-empty">{{ $invCopy['itinerary_empty'] ?? 'Muy pronto anotaré la agenda del día.' }}</p>
        @endif
    </div>
</section>
