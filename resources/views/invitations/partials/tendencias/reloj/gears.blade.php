{{--
    Padrinos de «A la misma hora»: el mecanismo. Cada padrino es un engranaje de latón con su inicial (los
    de lugar par giran al revés, como engranajes que se muerden), con su papel, sus nombres y su mensaje;
    el cortejo va en dos columnas. Recibe $data (módulo destacados).
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
    $initialOf = function (string $names): string {
        $words = array_values(array_filter(preg_split('/\s+/u', trim($names)) ?: [], fn ($word) => ! preg_match('/^(sr|sra|srta|dr|dra|don|doña|lic|ing)\.?$/iu', $word)));

        return mb_strtoupper(mb_substr($words[0] ?? $names, 0, 1));
    };
@endphp

<section class="inv-section reveal rl-gears-section" id="destacados">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['court_eyebrow'] ?? 'El mecanismo',
            'title' => $invCopy['court_title'] ?? 'Quienes hacen que todo marche',
            'intro' => $invCopy['court_intro'] ?? null,
        ])

        @if(count($padrinos) || count($court))
            @if(count($padrinos))
                <ul class="rl-sponsors">
                    @foreach($padrinos as $index => $padrino)
                        <li class="rl-sponsor" data-step style="--step: {{ $index }}">
                            <span class="rl-sponsor__gear" aria-hidden="true">
                                <span class="rl-gear"><i></i></span>
                                <b>{{ $initialOf($padrino['nombres']) }}</b>
                            </span>
                            @if(!empty($padrino['rol']))
                                <span class="rl-sponsor__role">{{ $padrino['rol'] }}</span>
                            @endif
                            <span class="rl-sponsor__names">{{ $padrino['nombres'] }}</span>
                            @if(!empty($padrino['mensaje']))
                                <span class="rl-sponsor__message">{{ $padrino['mensaje'] }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif

            @if(count($court))
                <div class="rl-court">
                    @foreach($court as $label => $people)
                        <div class="rl-court__group">
                            <h3 class="rl-court__title">{{ $label }}</h3>
                            <ul class="rl-court__list">
                                @foreach($people as $person)
                                    <li>
                                        <span class="rl-court__name">{{ $person['nombre'] }}</span>
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
