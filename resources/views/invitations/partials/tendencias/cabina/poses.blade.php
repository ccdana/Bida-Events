{{--
    Itinerario de «Cabina de fotos»: la noche, pose por pose. Cada momento es un cuadro de la tira, con
    su ícono como si fuera la foto y la hora en el sello naranja; debajo, la hora, lo que pasa y su
    descripción. Los cuadros se revelan en tonos distintos, como fotos de distintas poses. Recibe $data
    (módulo itinerario).
--}}
@php
    $eventos = $data['eventos'] ?? [];
@endphp

<section class="inv-section reveal cb-poses-section" id="itinerario">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['itinerary_eyebrow'] ?? 'La noche, pose por pose',
            'title' => $data['titulo'] ?? 'Itinerario',
        ])

        @if(count($eventos) > 0)
            <ol class="cb-poses">
                @foreach($eventos as $index => $evento)
                    <li class="cb-pose" data-step style="--step: {{ $index }}">
                        <span class="cb-pose__frame" aria-hidden="true">
                            @include('invitations.partials.itinerary-icon', ['name' => $evento['icono'] ?? null, 'class' => 'cb-pose__icon'])
                            @if(!empty($evento['hora']))
                                <span class="cb-pose__stamp">{{ $evento['hora'] }}</span>
                            @endif
                        </span>
                        <div class="cb-pose__caption">
                            @if(!empty($evento['hora']))
                                <time class="cb-pose__time">{{ $evento['hora'] }}</time>
                            @endif
                            <h3 class="cb-pose__title">{{ $evento['titulo'] ?? '' }}</h3>
                            @if(!empty($evento['descripcion']))
                                <p class="cb-pose__text">{{ $evento['descripcion'] }}</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        @else
            <p class="inv-empty">{{ $invCopy['itinerary_empty'] ?? 'Muy pronto compartiré cada momento de la noche.' }}</p>
        @endif
    </div>
</section>
