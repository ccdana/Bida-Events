{{--
    Padrinos de «Caleidoscopio»: el rosetón. Cada padrino en su celda de cristal con un hexágono de
    borde prismático y su inicial, su papel, sus nombres y su mensaje; la corte, en dos columnas con un
    rombo de color. Recibe $data (módulo destacados).
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
    $initialOf = function (string $names): string {
        $words = array_values(array_filter(preg_split('/\s+/u', trim($names)) ?: [], fn ($word) => ! preg_match('/^(sr|sra|srta|dr|dra|don|doña|lic|ing)\.?$/iu', $word)));

        return mb_strtoupper(mb_substr($words[0] ?? $names, 0, 1));
    };
@endphp

<section class="inv-section reveal ka-rosette-section" id="destacados">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['court_eyebrow'] ?? 'Quienes me acompañan',
            'title' => $invCopy['court_title'] ?? 'Mi corte de honor',
            'intro' => $invCopy['court_intro'] ?? null,
        ])

        @if(count($padrinos) || count($court))
            @if(count($padrinos))
                <ul class="ka-rosette">
                    @foreach($padrinos as $index => $padrino)
                        <li class="ka-sponsor" data-step style="--step: {{ $index }}">
                            <span class="ka-sponsor__badge" aria-hidden="true"><b>{{ $initialOf($padrino['nombres']) }}</b></span>
                            @if(!empty($padrino['rol']))
                                <span class="ka-sponsor__role">{{ $padrino['rol'] }}</span>
                            @endif
                            <span class="ka-sponsor__names">{{ $padrino['nombres'] }}</span>
                            @if(!empty($padrino['mensaje']))
                                <span class="ka-sponsor__message">{{ $padrino['mensaje'] }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif

            @if(count($court))
                <div class="ka-court">
                    @foreach($court as $label => $people)
                        <div class="ka-court__group">
                            <h3 class="ka-court__title">{{ $label }}</h3>
                            <ul class="ka-court__list">
                                @foreach($people as $person)
                                    <li>
                                        <span class="ka-court__name">{{ $person['nombre'] }}</span>
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
