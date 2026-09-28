{{--
    Padrinos de «Mapa de estrellas»: las estrellas que lo guían. Cada par de padrinos es una estrella
    grande que brilla, con su papel, sus nombres y su mensaje; la familia, estrellas más chicas en dos
    columnas. Recibe $data (módulo destacados).
--}}
@php
    $normalizePerson = fn ($persona) => is_array($persona)
        ? ['nombre' => trim((string) ($persona['nombre'] ?? '')), 'detalle' => $persona['detalle'] ?? null]
        : ['nombre' => trim((string) $persona), 'detalle' => null];
    $padrinos = array_values(array_filter($data['padrinos'] ?? [], fn ($padrino) => !empty($padrino['nombres'] ?? null)));
    $family = array_filter([
        ($invCopy['court_men'] ?? 'Abuelos') => array_values(array_filter(array_map($normalizePerson, $data['chambelanes'] ?? []), fn ($person) => $person['nombre'] !== '')),
        ($invCopy['court_women'] ?? 'Tíos') => array_values(array_filter(array_map($normalizePerson, $data['damitas'] ?? []), fn ($person) => $person['nombre'] !== '')),
    ]);
@endphp

<section class="inv-section reveal es-guides-section" id="destacados">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['court_eyebrow'] ?? 'Las estrellas que me guían',
            'title' => $invCopy['court_title'] ?? 'Mis padrinos',
            'intro' => $invCopy['court_intro'] ?? null,
        ])

        @if(count($padrinos) || count($family))
            @if(count($padrinos))
                <ul class="es-guides">
                    @foreach($padrinos as $index => $padrino)
                        <li class="es-guide" data-step style="--step: {{ $index }}">
                            <span class="es-guide__star" aria-hidden="true"></span>
                            @if(!empty($padrino['rol']))
                                <span class="es-guide__role">{{ $padrino['rol'] }}</span>
                            @endif
                            <span class="es-guide__names">{{ $padrino['nombres'] }}</span>
                            @if(!empty($padrino['mensaje']))
                                <p class="es-guide__message">{{ $padrino['mensaje'] }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif

            @if(count($family))
                <div class="es-family">
                    @foreach($family as $label => $people)
                        <div class="es-family__group">
                            <h3 class="es-family__title">{{ $label }}</h3>
                            <ul class="es-family__list">
                                @foreach($people as $person)
                                    <li>
                                        <span class="es-family__name">{{ $person['nombre'] }}</span>
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
            <p class="inv-empty">{{ $invCopy['court_empty'] ?? 'Pronto presentaremos a mis padrinos.' }}</p>
        @endif
    </div>
</section>
