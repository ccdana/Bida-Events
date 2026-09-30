{{--
    Itinerario de «Esencia XV»: la pirámide olfativa de la noche. Los momentos se reparten en tres
    pisos, del más angosto al más ancho: notas de salida (lo primero de la noche), de corazón y de
    fondo (lo último), cada piso con su nombre y sus momentos (hora, qué pasa y su descripción). Si el
    reparto no es parejo, los pisos de abajo llevan uno más. Recibe $data (módulo itinerario).
--}}
@php
    $eventos = array_values($data['eventos'] ?? []);
    $total = count($eventos);
    // Tres pisos: arriba el más corto; lo que sobra va a los de abajo
    $base = intdiv($total, 3);
    $extra = $total % 3;
    $sizes = [$base, $base + ($extra === 2 ? 1 : 0), $base + ($extra > 0 ? 1 : 0)];
    $tiers = [];
    $offset = 0;
    foreach ([
        $invCopy['scent_top'] ?? 'Notas de salida',
        $invCopy['scent_heart'] ?? 'Notas de corazón',
        $invCopy['scent_base'] ?? 'Notas de fondo',
    ] as $index => $label) {
        $moments = array_slice($eventos, $offset, $sizes[$index]);
        $offset += $sizes[$index];
        if ($moments) {
            $tiers[] = ['label' => $label, 'moments' => $moments, 'level' => $index];
        }
    }
@endphp

<section class="inv-section reveal ez-pyramid-section" id="itinerario">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['itinerary_eyebrow'] ?? 'La pirámide de la noche',
            'title' => $data['titulo'] ?? 'Itinerario',
        ])

        @if($total > 0)
            <div class="ez-pyramid">
                @foreach($tiers as $tier)
                    <div class="ez-tier ez-tier--{{ $tier['level'] }}" data-step style="--step: {{ $loop->index }}">
                        <h3 class="ez-tier__label">{{ $tier['label'] }}</h3>
                        <ol class="ez-tier__list">
                            @foreach($tier['moments'] as $evento)
                                <li class="ez-note">
                                    <time class="ez-note__time">{{ $evento['hora'] ?? '' }}</time>
                                    <span class="ez-note__title">{{ $evento['titulo'] ?? '' }}</span>
                                    @if(!empty($evento['descripcion']))
                                        <span class="ez-note__text">{{ $evento['descripcion'] }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    </div>
                @endforeach
            </div>
        @else
            <p class="inv-empty">{{ $invCopy['itinerary_empty'] ?? 'Muy pronto compartiré el programa de la noche.' }}</p>
        @endif
    </div>
</section>
