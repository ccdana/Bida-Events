{{--
    Itinerario de «Caldero encantado»: la receta de la noche, paso a paso. Cada momento es un paso
    numerado con su frasquito (cada uno de un color de la paleta) y la hora como la medida del
    ingrediente; una gota cae de paso en paso por la línea punteada. Recibe $data (módulo itinerario).
--}}
@php
    $eventos = $data['eventos'] ?? [];
@endphp

<section class="inv-section reveal cl-recipe-section" id="itinerario">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['itinerary_eyebrow'] ?? 'La receta de la noche',
            'title' => $data['titulo'] ?? 'Itinerario',
        ])

        @if(count($eventos) > 0)
            <ol class="cl-recipe">
                @foreach($eventos as $index => $evento)
                    <li class="cl-step is-tone-{{ $index % 3 + 1 }}" data-step style="--step: {{ $index }}">
                        <span class="cl-step__vial" aria-hidden="true"><i></i></span>
                        <div class="cl-step__body">
                            <p class="cl-step__meta">
                                <span class="cl-step__number">{{ $invCopy['potion_step'] ?? 'Paso' }} {{ $index + 1 }}</span>
                                <time class="cl-step__time">{{ $evento['hora'] ?? '' }}</time>
                            </p>
                            <h3 class="cl-step__title">{{ $evento['titulo'] ?? '' }}</h3>
                            @if(!empty($evento['descripcion']))
                                <p class="cl-step__text">{{ $evento['descripcion'] }}</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        @else
            <p class="inv-empty">{{ $invCopy['itinerary_empty'] ?? 'Muy pronto publicaremos la receta de la noche.' }}</p>
        @endif
    </div>
</section>
