{{--
    Gente favorita de «Día feriado»: los papelitos pegados en el almanaque. Cada anfitrión tiene su
    papelito con cinta, con su papel, sus nombres escritos a mano y su mensaje; cada grupo (familia,
    amigos) es una lista en su papelito, con un visto de bolígrafo junto a cada nombre. Recibe $data
    (módulo destacados).
--}}
@php
    $normalizePerson = fn ($persona) => is_array($persona)
        ? ['nombre' => trim((string) ($persona['nombre'] ?? '')), 'detalle' => $persona['detalle'] ?? null]
        : ['nombre' => trim((string) $persona), 'detalle' => null];
    $padrinos = array_values(array_filter($data['padrinos'] ?? [], fn ($padrino) => !empty($padrino['nombres'] ?? null)));
    $court = array_filter([
        ($invCopy['court_women'] ?? 'Familia') => array_values(array_filter(array_map($normalizePerson, $data['damitas'] ?? []), fn ($person) => $person['nombre'] !== '')),
        ($invCopy['court_men'] ?? 'Amigos') => array_values(array_filter(array_map($normalizePerson, $data['chambelanes'] ?? []), fn ($person) => $person['nombre'] !== '')),
    ]);
@endphp

<section class="inv-section reveal fd-notes-section" id="destacados">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['court_eyebrow'] ?? 'Anotados en mi almanaque',
            'title' => $invCopy['court_title'] ?? 'Mi gente favorita',
            'intro' => $invCopy['court_intro'] ?? null,
        ])

        @if(count($padrinos) || count($court))
            @if(count($padrinos))
                <ul class="fd-notes">
                    @foreach($padrinos as $index => $padrino)
                        <li class="fd-note" data-step style="--step: {{ $index }}">
                            <span class="fd-note__tape" aria-hidden="true"></span>
                            @if(!empty($padrino['rol']))
                                <span class="fd-note__role">{{ $padrino['rol'] }}</span>
                            @endif
                            <span class="fd-note__names">{{ $padrino['nombres'] }}</span>
                            @if(!empty($padrino['mensaje']))
                                <span class="fd-note__message">{{ $padrino['mensaje'] }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif

            @if(count($court))
                <div class="fd-lists">
                    @foreach($court as $label => $people)
                        <div class="fd-list" data-step style="--step: {{ count($padrinos) + $loop->index }}">
                            <span class="fd-note__tape" aria-hidden="true"></span>
                            <h3 class="fd-list__title">{{ $label }}</h3>
                            <ul class="fd-list__items">
                                @foreach($people as $person)
                                    <li>
                                        <svg class="fd-list__check" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M4 13.5l5 5L20 5"/></svg>
                                        <span>
                                            <span class="fd-list__name">{{ $person['nombre'] }}</span>
                                            @if(!empty($person['detalle']))
                                                <small>{{ $person['detalle'] }}</small>
                                            @endif
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            @endif
        @else
            <p class="inv-empty">{{ $invCopy['court_empty'] ?? 'Pronto presentaré a mi gente favorita.' }}</p>
        @endif
    </div>
</section>
