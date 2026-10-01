{{--
    Itinerario de «El cambio de zapatos»: los pasos de la noche. Cada momento es una huella de tacón
    (la planta y el tacón, con el número del paso adentro) que va alternando izquierda y derecha como
    quien camina; al lado, la hora, lo que pasa y su descripción. El momento del cambio de zapatos
    (si el programa lo tiene: «zapato», «tacón») lleva la huella llena y su sello. Recibe $data
    (módulo itinerario).
--}}
@php
    $eventos = array_values($data['eventos'] ?? []);
    $isShoeMoment = fn (array $evento) => (bool) preg_match('/zapat|tac[oó]n/iu', ($evento['titulo'] ?? '').' '.($evento['descripcion'] ?? ''));
@endphp

<section class="inv-section reveal zp-steps-section" id="itinerario">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['itinerary_eyebrow'] ?? 'Los pasos de la noche',
            'title' => $data['titulo'] ?? 'Itinerario',
        ])

        @if(count($eventos) > 0)
            <ol class="zp-steps" style="--steps: {{ count($eventos) }}">
                @foreach($eventos as $index => $evento)
                    @php($highlight = $isShoeMoment($evento))
                    <li @class(['zp-step', 'zp-step--right' => $index % 2 === 1, 'is-moment' => $highlight]) data-step style="--step: {{ $index }}">
                        <span class="zp-step__print" aria-hidden="true">
                            <svg viewBox="0 0 30 72" focusable="false">
                                <path class="zp-step__sole" d="M15 2 C24 2 28 13 27 26 C26 37 22 44 15 44 C8 44 4 37 3 26 C2 13 6 2 15 2 Z"/>
                                <ellipse class="zp-step__heel" cx="15" cy="62" rx="5" ry="6"/>
                                <text class="zp-step__number" x="15" y="25" text-anchor="middle" dominant-baseline="central">{{ $index + 1 }}</text>
                            </svg>
                        </span>
                        <div class="zp-step__body">
                            @if($highlight)
                                <span class="zp-step__seal">{{ $invCopy['shoe_moment'] ?? 'El cambio de zapatos' }}</span>
                            @endif
                            <time class="zp-step__time">{{ $evento['hora'] ?? '' }}</time>
                            <h3 class="zp-step__title">{{ $evento['titulo'] ?? '' }}</h3>
                            @if(!empty($evento['descripcion']))
                                <p class="zp-step__text">{{ $evento['descripcion'] }}</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        @else
            <p class="inv-empty">{{ $invCopy['itinerary_empty'] ?? 'Muy pronto compartiré los pasos de la noche.' }}</p>
        @endif
    </div>
</section>
