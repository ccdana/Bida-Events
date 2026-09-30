{{--
    Itinerario de «Mesa de honor»: el menú del día. Una tarjeta de menú con cada momento servido como un
    tiempo de la cena (Primer tiempo, Segundo tiempo…): la hora, lo que pasa y su descripción, separados
    por un filete de oro con su rombo. Recibe $data (módulo itinerario).
--}}
@php
    $eventos = $data['eventos'] ?? [];
    $ordinals = ['Primer', 'Segundo', 'Tercer', 'Cuarto', 'Quinto', 'Sexto', 'Séptimo', 'Octavo', 'Noveno', 'Décimo'];
    $course = $invCopy['table_course'] ?? 'tiempo';
@endphp

<section class="inv-section reveal ms-menu-section" id="itinerario">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['itinerary_eyebrow'] ?? 'El menú del día',
            'title' => $data['titulo'] ?? 'Itinerario',
        ])

        @if(count($eventos) > 0)
            <ol class="ms-courses">
                @foreach($eventos as $index => $evento)
                    <li class="ms-course" data-step style="--step: {{ $index }}">
                        <span class="ms-course__number">{{ isset($ordinals[$index]) ? $ordinals[$index].' '.$course : ($index + 1).'.º '.$course }}</span>
                        <time class="ms-course__time">{{ $evento['hora'] ?? '' }}</time>
                        <h3 class="ms-course__title">{{ $evento['titulo'] ?? '' }}</h3>
                        @if(!empty($evento['descripcion']))
                            <p class="ms-course__text">{{ $evento['descripcion'] }}</p>
                        @endif
                    </li>
                @endforeach
            </ol>
        @else
            <p class="inv-empty">{{ $invCopy['itinerary_empty'] ?? 'Muy pronto compartiremos el menú del día.' }}</p>
        @endif
    </div>
</section>
