{{--
    Anfitriones de «Caldero encantado»: quienes preparan la poción, cada uno con su frasco en el
    estante (de un color distinto, con su papel escrito en la etiqueta), sus nombres y su mensaje. Los
    grupos (el show, el jurado…) van como listas de ingredientes con burbujitas. Recibe $data
    (módulo destacados).
--}}
@php
    $normalizePerson = fn ($persona) => is_array($persona)
        ? ['nombre' => trim((string) ($persona['nombre'] ?? '')), 'detalle' => $persona['detalle'] ?? null]
        : ['nombre' => trim((string) $persona), 'detalle' => null];
    $padrinos = array_values(array_filter($data['padrinos'] ?? [], fn ($padrino) => !empty($padrino['nombres'] ?? null)));
    $groups = array_filter([
        ($invCopy['court_men'] ?? 'El show') => array_values(array_filter(array_map($normalizePerson, $data['chambelanes'] ?? []), fn ($person) => $person['nombre'] !== '')),
        ($invCopy['court_women'] ?? 'El jurado') => array_values(array_filter(array_map($normalizePerson, $data['damitas'] ?? []), fn ($person) => $person['nombre'] !== '')),
    ]);
@endphp

<section class="inv-section reveal cl-crew-section" id="destacados">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['court_eyebrow'] ?? 'Los aprendices del caldero',
            'title' => $invCopy['court_title'] ?? 'Quién prepara la poción',
            'intro' => $invCopy['court_intro'] ?? null,
        ])

        @if(count($padrinos) || count($groups))
            @if(count($padrinos))
                <ul class="cl-crew">
                    @foreach($padrinos as $index => $padrino)
                        <li class="cl-brewer is-tone-{{ $index % 3 + 1 }}" data-step style="--step: {{ $index }}">
                            <span class="cl-brewer__bottle" aria-hidden="true">
                                <span class="cl-brewer__cork"></span>
                                <span class="cl-brewer__glass"><i></i><i></i><i></i></span>
                            </span>
                            @if(!empty($padrino['rol']))
                                <span class="cl-brewer__role">{{ $padrino['rol'] }}</span>
                            @endif
                            <span class="cl-brewer__names">{{ $padrino['nombres'] }}</span>
                            @if(!empty($padrino['mensaje']))
                                <p class="cl-brewer__message">{{ $padrino['mensaje'] }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif

            @if(count($groups))
                <div class="cl-groups">
                    @foreach($groups as $label => $people)
                        <div class="cl-group">
                            <h3 class="cl-group__title">{{ $label }}</h3>
                            <ul class="cl-group__list">
                                @foreach($people as $person)
                                    <li>
                                        <span class="cl-group__name">{{ $person['nombre'] }}</span>
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
            <p class="inv-empty">{{ $invCopy['court_empty'] ?? 'Pronto presentaremos a quienes preparan la fiesta.' }}</p>
        @endif
    </div>
</section>
