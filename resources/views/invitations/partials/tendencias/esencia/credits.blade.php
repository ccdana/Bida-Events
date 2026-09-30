{{--
    Padrinos de «Esencia XV»: los créditos del reverso de la caja. Arriba la franja de la caja con la
    fragancia y su nombre; debajo, cada padrino como una línea de la lista (su papel, unos puntos que
    llevan hasta sus nombres, y su mensaje). La corte va en dos columnas. Recibe $data (módulo destacados).
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

<section class="inv-section reveal ez-credits-section" id="destacados">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['court_eyebrow'] ?? 'La casa detrás de la fragancia',
            'title' => $invCopy['court_title'] ?? 'Créditos',
            'intro' => $invCopy['court_intro'] ?? null,
        ])

        @if(count($padrinos) || count($court))
            <div class="ez-credits">
                <p class="ez-credits__band">
                    <span>{{ $invCopy['scent_line'] ?? 'Eau de Quince' }}</span>
                    <span>{{ $page->displayName }}</span>
                </p>

                @if(count($padrinos))
                    <ul class="ez-credits__list">
                        @foreach($padrinos as $index => $padrino)
                            <li class="ez-credit" data-step style="--step: {{ $index }}">
                                <span class="ez-credit__line">
                                    <span class="ez-credit__role">{{ $padrino['rol'] ?? '' }}</span>
                                    <span class="ez-credit__dots" aria-hidden="true"></span>
                                    <span class="ez-credit__names">{{ $padrino['nombres'] }}</span>
                                </span>
                                @if(!empty($padrino['mensaje']))
                                    <span class="ez-credit__message">{{ $padrino['mensaje'] }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if(count($court))
                    <div class="ez-court">
                        @foreach($court as $label => $people)
                            <div class="ez-court__group">
                                <h3 class="ez-court__title">{{ $label }}</h3>
                                <ul class="ez-court__list">
                                    @foreach($people as $person)
                                        <li>
                                            <span class="ez-court__name">{{ $person['nombre'] }}</span>
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
            </div>
        @else
            <p class="inv-empty">{{ $invCopy['court_empty'] ?? 'Pronto presentaré a mi corte de honor.' }}</p>
        @endif
    </div>
</section>
