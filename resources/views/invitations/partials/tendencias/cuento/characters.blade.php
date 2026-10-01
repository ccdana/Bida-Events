{{--
    Padrinos de «Cuento desplegable»: los personajes del cuento. Cada padrino tiene su medallón de papel
    recortado con la inicial, una cinta con su papel en la historia, sus nombres y su mensaje. La corte
    va como el reparto del cuento, en dos columnas con su viñeta. Recibe $data (módulo destacados).
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

<section class="inv-section reveal cu-characters-section" id="destacados">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['court_eyebrow'] ?? 'Los personajes',
            'title' => $invCopy['court_title'] ?? 'Quienes están en mi historia',
            'intro' => $invCopy['court_intro'] ?? null,
        ])

        @if(count($padrinos) || count($court))
            @if(count($padrinos))
                <ul class="cu-characters">
                    @foreach($padrinos as $index => $padrino)
                        <li class="cu-character" data-step style="--step: {{ $index }}">
                            <span class="cu-character__medal" data-poke="pop" aria-hidden="true">{{ $initialOf($padrino['nombres']) }}</span>
                            @if(!empty($padrino['rol']))
                                <span class="cu-character__role">{{ $padrino['rol'] }}</span>
                            @endif
                            <span class="cu-character__names">{{ $padrino['nombres'] }}</span>
                            @if(!empty($padrino['mensaje']))
                                <span class="cu-character__message">{{ $padrino['mensaje'] }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif

            @if(count($court))
                <div class="cu-cast">
                    @foreach($court as $label => $people)
                        <div class="cu-cast__group">
                            <h3 class="cu-cast__title">{{ $label }}</h3>
                            <ul class="cu-cast__list">
                                @foreach($people as $person)
                                    <li style="--i: {{ $loop->index }}">
                                        <span class="cu-cast__name">{{ $person['nombre'] }}</span>
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
