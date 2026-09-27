{{--
    Padrinos y cortejo de «Galería Quince»: el muro de mecenas que hay a la entrada de los museos.
    Los padrinos van grabados en la pared, cada uno con su papel y, si lo tiene, su mensaje; debajo,
    el cortejo en dos columnas, como la lista de colaboradores. Todo a la vista, sin pestañas.
    Recibe $data (módulo destacados).
--}}
@php
    $normalizePerson = fn ($persona) => is_array($persona)
        ? ['nombre' => trim((string) ($persona['nombre'] ?? '')), 'detalle' => $persona['detalle'] ?? null]
        : ['nombre' => trim((string) $persona), 'detalle' => null];
    $padrinos = array_values(array_filter($data['padrinos'] ?? [], fn ($padrino) => !empty($padrino['nombres'] ?? null)));
    $groups = array_filter([
        ($invCopy['court_men'] ?? 'Chambelanes') => array_values(array_filter(array_map($normalizePerson, $data['chambelanes'] ?? []), fn ($person) => $person['nombre'] !== '')),
        ($invCopy['court_women'] ?? 'Damitas') => array_values(array_filter(array_map($normalizePerson, $data['damitas'] ?? []), fn ($person) => $person['nombre'] !== '')),
    ]);
@endphp

<section class="inv-section reveal gq-patrons" id="destacados">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['court_eyebrow'] ?? 'Con el apoyo de',
            'title' => $invCopy['court_title'] ?? 'Mecenas de la exposición',
            'intro' => $invCopy['court_intro'] ?? 'Las personas que hicieron posible esta noche.',
        ])

        @if(count($padrinos) || count($groups))
            <div class="gq-wall">
                @if(count($padrinos))
                    <ul class="gq-donors">
                        @foreach($padrinos as $index => $padrino)
                            <li class="gq-donor" data-step style="--step: {{ $index }}">
                                @if(!empty($padrino['rol']))
                                    <span class="gq-donor__role">{{ $padrino['rol'] }}</span>
                                @endif
                                <span class="gq-donor__names">{{ $padrino['nombres'] }}</span>
                                @if(!empty($padrino['mensaje']))
                                    <span class="gq-donor__message">«{{ $padrino['mensaje'] }}»</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if(count($groups))
                    <div class="gq-circles">
                        @foreach($groups as $label => $people)
                            <div class="gq-circle">
                                <h3 class="gq-circle__title">{{ $label }}</h3>
                                <ul class="gq-circle__names">
                                    @foreach($people as $person)
                                        <li>
                                            <span>{{ $person['nombre'] }}</span>
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
            <p class="inv-empty">{{ $invCopy['court_empty'] ?? 'Pronto presentaremos a quienes me acompañan.' }}</p>
        @endif
    </div>
</section>
