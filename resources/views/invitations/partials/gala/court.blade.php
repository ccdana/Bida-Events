{{--
    Corte de «Noche de gala»: los padrinos en placas grabadas, como las del salón, con su papel y su
    mensaje; debajo la corte de honor en dos columnas, damas y caballeros, cada nombre con su rombo.
    Recibe $data (módulo destacados).
--}}
@php
    $normalizePerson = fn ($persona) => is_array($persona)
        ? ['nombre' => trim((string) ($persona['nombre'] ?? '')), 'detalle' => $persona['detalle'] ?? null]
        : ['nombre' => trim((string) $persona), 'detalle' => null];
    $padrinos = array_values(array_filter($data['padrinos'] ?? [], fn ($padrino) => !empty($padrino['nombres'] ?? null)));
    $court = array_filter([
        ($invCopy['court_women'] ?? 'Damas') => array_values(array_filter(array_map($normalizePerson, $data['damitas'] ?? []), fn ($person) => $person['nombre'] !== '')),
        ($invCopy['court_men'] ?? 'Chambelanes') => array_values(array_filter(array_map($normalizePerson, $data['chambelanes'] ?? []), fn ($person) => $person['nombre'] !== '')),
    ]);
@endphp

<section class="inv-section reveal ga-court-section" id="destacados">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['court_eyebrow'] ?? 'Quienes me acompañan',
            'title' => $invCopy['court_title'] ?? 'Mi corte de honor',
            'intro' => $invCopy['court_intro'] ?? null,
        ])

        @if(count($padrinos) || count($court))
            @if(count($padrinos))
                <ul class="ga-plaques">
                    @foreach($padrinos as $index => $padrino)
                        <li class="ga-plaque ga-plaque--small" data-step style="--step: {{ $index }}">
                            @if(!empty($padrino['rol']))
                                <span class="ga-plaque__role">{{ $padrino['rol'] }}</span>
                            @endif
                            <span class="ga-plaque__names">{{ $padrino['nombres'] }}</span>
                            @if(!empty($padrino['mensaje']))
                                <span class="ga-plaque__message">{{ $padrino['mensaje'] }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif

            @if(count($court))
                <div class="ga-court">
                    @foreach($court as $label => $people)
                        <div class="ga-court__column">
                            <h3 class="ga-court__title">{{ $label }}</h3>
                            <ul class="ga-court__list">
                                @foreach($people as $person)
                                    <li>
                                        <span class="ga-court__name">{{ $person['nombre'] }}</span>
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
            <p class="inv-empty">{{ $invCopy['court_empty'] ?? 'Pronto presentaré a mi corte de honor.' }}</p>
        @endif
    </div>
</section>
