{{--
    Itinerario de «Atelier»: el orden del desfile sobre una cinta métrica. La cinta baja por la
    izquierda con sus marcas; cada momento es un look (Look 01, 02…) con su hora a la altura de su
    marca y un alfiler clavado en la cinta. Recibe $data (módulo itinerario).
--}}
@php
    $eventos = $data['eventos'] ?? [];
@endphp

<section class="inv-section reveal at-show-section" id="itinerario">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['itinerary_eyebrow'] ?? 'El orden del desfile',
            'title' => $data['titulo'] ?? 'Itinerario',
        ])

        @if(count($eventos) > 0)
            <ol class="at-show">
                @foreach($eventos as $index => $evento)
                    <li class="at-look" data-step style="--step: {{ $index }}">
                        <time class="at-look__time">{{ $evento['hora'] ?? '' }}</time>
                        <span class="at-look__pin" aria-hidden="true"></span>
                        <div class="at-look__body">
                            <span class="at-look__number">{{ $invCopy['atelier_look'] ?? 'Look' }} {{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                            <h3 class="at-look__title">{{ $evento['titulo'] ?? '' }}</h3>
                            @if(!empty($evento['descripcion']))
                                <p class="at-look__text">{{ $evento['descripcion'] }}</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        @else
            <p class="inv-empty">{{ $invCopy['itinerary_empty'] ?? 'Muy pronto publicaremos el orden del desfile.' }}</p>
        @endif
    </div>
</section>
