{{--
    Padrinos de «Mesa de honor»: la mesa de honor. Cada padrino tiene su tarjeta de lugar doblada en
    carpa, con su papel, sus nombres en caligrafía y su mensaje; el cortejo va en dos columnas, como la
    lista de la mesa. Recibe $data (módulo destacados).
--}}
@php
    $normalizePerson = fn ($persona) => is_array($persona)
        ? ['nombre' => trim((string) ($persona['nombre'] ?? '')), 'detalle' => $persona['detalle'] ?? null]
        : ['nombre' => trim((string) $persona), 'detalle' => null];
    $padrinos = array_values(array_filter($data['padrinos'] ?? [], fn ($padrino) => !empty($padrino['nombres'] ?? null)));
    $court = array_filter([
        ($invCopy['court_women'] ?? 'Damas de honor') => array_values(array_filter(array_map($normalizePerson, $data['damitas'] ?? []), fn ($person) => $person['nombre'] !== '')),
        ($invCopy['court_men'] ?? 'Caballeros de honor') => array_values(array_filter(array_map($normalizePerson, $data['chambelanes'] ?? []), fn ($person) => $person['nombre'] !== '')),
    ]);
@endphp

<section class="inv-section reveal ms-seating-section" id="destacados">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['court_eyebrow'] ?? 'Mesa de honor',
            'title' => $invCopy['court_title'] ?? 'Quienes se sientan a nuestro lado',
            'intro' => $invCopy['court_intro'] ?? null,
        ])

        @if(count($padrinos) || count($court))
            @if(count($padrinos))
                <ul class="ms-seats">
                    @foreach($padrinos as $index => $padrino)
                        <li class="ms-seat" data-step data-poke="tip" style="--step: {{ $index }}">
                            @if(!empty($padrino['rol']))
                                <span class="ms-seat__role">{{ $padrino['rol'] }}</span>
                            @endif
                            <span class="ms-seat__names">{{ $padrino['nombres'] }}</span>
                            @if(!empty($padrino['mensaje']))
                                <span class="ms-seat__message">{{ $padrino['mensaje'] }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif

            @if(count($court))
                <div class="ms-court">
                    @foreach($court as $label => $people)
                        <div class="ms-court__group">
                            <h3 class="ms-court__title">{{ $label }}</h3>
                            <ul class="ms-court__list">
                                @foreach($people as $person)
                                    <li style="--i: {{ $loop->index }}">
                                        <span class="ms-court__name">{{ $person['nombre'] }}</span>
                                        @if(!empty($person['detalle']))
                                            <small>{{ $person['detalle'] }}</small>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            @endif
        @else
            <p class="inv-empty">{{ $invCopy['court_empty'] ?? 'Pronto presentaremos a quienes nos acompañan.' }}</p>
        @endif
    </div>
</section>
