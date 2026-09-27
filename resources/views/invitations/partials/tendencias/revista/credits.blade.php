{{--
    Padrinos y cortejo de «Edición especial»: la caja de créditos de la revista, la que dice quién hizo
    cada cosa. Los padrinos van con su papel en versalitas y su nombre («Padrinos de promoción:
    …»), y el mensaje como una cita; la familia y los compañeros figuran como colaboradores de la
    edición. Todo a la vista, sin pestañas. Recibe $data (módulo destacados).
--}}
@php
    $normalizePerson = fn ($persona) => is_array($persona)
        ? ['nombre' => trim((string) ($persona['nombre'] ?? '')), 'detalle' => $persona['detalle'] ?? null]
        : ['nombre' => trim((string) $persona), 'detalle' => null];
    $padrinos = array_values(array_filter($data['padrinos'] ?? [], fn ($padrino) => !empty($padrino['nombres'] ?? null)));
    $groups = array_filter([
        ($invCopy['court_men'] ?? 'Familia') => array_values(array_filter(array_map($normalizePerson, $data['chambelanes'] ?? []), fn ($person) => $person['nombre'] !== '')),
        ($invCopy['court_women'] ?? 'Compañeros') => array_values(array_filter(array_map($normalizePerson, $data['damitas'] ?? []), fn ($person) => $person['nombre'] !== '')),
    ]);
@endphp

<section class="inv-section reveal rv-credits-section" id="destacados">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['court_eyebrow'] ?? 'Créditos de esta edición',
            'title' => $invCopy['court_title'] ?? 'Gracias a ustedes',
            'intro' => $invCopy['court_intro'] ?? null,
        ])

        @if(count($padrinos) || count($groups))
            <div class="rv-credits">
                @if(count($padrinos))
                    <dl class="rv-credits__list">
                        @foreach($padrinos as $padrino)
                            <div class="rv-credit">
                                <dt>{{ ($padrino['rol'] ?? null) ?: ($invCopy['court_sponsors_tab'] ?? 'Padrinos') }}</dt>
                                <dd>
                                    {{ $padrino['nombres'] }}
                                    @if(!empty($padrino['mensaje']))
                                        <q class="rv-credit__quote">{{ $padrino['mensaje'] }}</q>
                                    @endif
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                @endif

                @foreach($groups as $label => $people)
                    <div class="rv-credits__group">
                        <p class="rv-credits__group-title">{{ $label }}</p>
                        <p class="rv-credits__names">
                            @foreach($people as $person)
                                <span>{{ $person['nombre'] }}@if(!empty($person['detalle'])) <small>({{ $person['detalle'] }})</small>@endif</span>@if(! $loop->last)<span class="rv-credits__sep" aria-hidden="true"> · </span>@endif
                            @endforeach
                        </p>
                    </div>
                @endforeach
            </div>
        @else
            <p class="inv-empty">{{ $invCopy['court_empty'] ?? 'Pronto presentaré a quienes me acompañaron.' }}</p>
        @endif
    </div>
</section>
