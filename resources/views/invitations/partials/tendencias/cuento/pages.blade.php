{{--
    Itinerario de «Cuento desplegable»: las páginas de la noche. Cada momento es una página de papel
    que se levanta del libro al aparecer (como las piezas de un desplegable), con la hora en su pestaña
    de índice, lo que pasa y su ícono en un medallón de papel. Recibe $data (módulo itinerario).
--}}
@php
    $eventos = $data['eventos'] ?? [];
@endphp

<section class="inv-section reveal cu-pages-section" id="itinerario">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['itinerary_eyebrow'] ?? 'Las páginas de la noche',
            'title' => $data['titulo'] ?? 'Itinerario',
        ])

        @if(count($eventos) > 0)
            <ol class="cu-pages">
                @foreach($eventos as $index => $evento)
                    <li class="cu-page" data-step style="--step: {{ $index }}">
                        <time class="cu-page__tab">{{ $evento['hora'] ?? '' }}</time>
                        <div class="cu-page__body">
                            <h3 class="cu-page__title">{{ $evento['titulo'] ?? '' }}</h3>
                            @if(!empty($evento['descripcion']))
                                <p class="cu-page__text">{{ $evento['descripcion'] }}</p>
                            @endif
                        </div>
                        <span class="cu-page__icon" data-poke="pop" aria-hidden="true">
                            @include('invitations.partials.itinerary-icon', ['name' => $evento['icono'] ?? null, 'class' => 'cu-page__svg'])
                        </span>
                    </li>
                @endforeach
            </ol>
        @else
            <p class="inv-empty">{{ $invCopy['itinerary_empty'] ?? 'Muy pronto escribiremos las páginas de la noche.' }}</p>
        @endif
    </div>
</section>
