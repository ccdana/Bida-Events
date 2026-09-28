{{--
    Padrinos de «Bordado a mano»: cada par de padrinos tiene su bastidor chiquito con sus iniciales
    bordadas en monograma y una ramita; debajo, su papel, sus nombres y su mensaje. La familia va en
    dos listas marcadas con punto cruz. Recibe $data (módulo destacados).
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
    // Monograma: la inicial de cada nombre («Natalia Peña y Javier Soliz» → N & J; «Sr. Ernesto…» → E)
    $monogram = function (string $names): string {
        $people = preg_split('/\s+(?:y|e|&)\s+|\s*,\s*/u', trim($names)) ?: [];
        $initials = [];
        foreach ($people as $person) {
            $words = array_values(array_filter(preg_split('/\s+/u', $person) ?: [], fn ($word) => ! preg_match('/^(sr|sra|srta|dr|dra|don|doña|lic|ing)\.?$/iu', $word)));
            if ($words) {
                $initials[] = mb_strtoupper(mb_substr($words[0], 0, 1));
            }
        }

        return implode(' & ', array_slice($initials, 0, 2));
    };
@endphp

<section class="inv-section reveal bd-monograms-section" id="destacados">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['court_eyebrow'] ?? 'Quienes me guiarán',
            'title' => $invCopy['court_title'] ?? 'Mis padrinos',
            'intro' => $invCopy['court_intro'] ?? null,
        ])

        @if(count($padrinos) || count($family))
            @if(count($padrinos))
                <ul class="bd-monograms">
                    @foreach($padrinos as $index => $padrino)
                        <li class="bd-monogram" data-step style="--step: {{ $index }}">
                            <span class="bd-hoop bd-hoop--mini" aria-hidden="true">
                                <span class="bd-hoop__clasp"><i></i></span>
                                <span class="bd-hoop__cloth">
                                    <svg class="bd-monogram__sprig" viewBox="0 0 100 100" focusable="false">
                                        <path class="bd-monogram__stem" d="M22 78 C38 88 62 88 78 78"/>
                                        @foreach([[30, 82, 200], [40, 86, 160], [60, 86, 20], [70, 82, -20]] as [$x, $y, $rotate])
                                            <path class="bd-wreath__leaf" d="M0 0 C2.5 -3 7 -3.5 10 0 C7 3.5 2.5 3 0 0 Z" transform="translate({{ $x }} {{ $y }}) rotate({{ $rotate }})"/>
                                        @endforeach
                                        @include('invitations.partials.tendencias.bordado.flower', ['x' => 50, 'y' => 86, 'size' => 0.5, 'class' => $index % 2 ? 'is-thread' : ''])
                                    </svg>
                                    <span class="bd-monogram__letters bd-satin">{{ $monogram($padrino['nombres']) }}</span>
                                </span>
                            </span>
                            @if(!empty($padrino['rol']))
                                <span class="bd-monogram__role">{{ $padrino['rol'] }}</span>
                            @endif
                            <span class="bd-monogram__names">{{ $padrino['nombres'] }}</span>
                            @if(!empty($padrino['mensaje']))
                                <p class="bd-monogram__message">{{ $padrino['mensaje'] }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif

            @if(count($family))
                <div class="bd-family">
                    @foreach($family as $label => $people)
                        <div class="bd-family__group">
                            <h3 class="bd-family__title">{{ $label }}</h3>
                            <ul class="bd-family__list">
                                @foreach($people as $person)
                                    <li>
                                        <span class="bd-family__name">{{ $person['nombre'] }}</span>
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
