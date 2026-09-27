{{--
    Agradecimientos de «Birrete al aire»: este logro también es de ellos, así que cada uno recibe su
    medalla. Los padrinos, una grande con la cinta, sus iniciales grabadas, el papel y su mensaje; la
    familia y los compañeros, medallas chicas de a dos. Recibe $data (módulo destacados).
--}}
@php
    $normalizePerson = fn ($persona) => is_array($persona)
        ? ['nombre' => trim((string) ($persona['nombre'] ?? '')), 'detalle' => $persona['detalle'] ?? null]
        : ['nombre' => trim((string) $persona), 'detalle' => null];
    // Iniciales grabadas: de una pareja, la de cada uno; de una persona, nombre y apellido (sin «Sr.», «Arq.»…)
    $initials = function (string $names): string {
        $clean = trim(preg_replace('/\b(?:Sr|Sra|Srta|Dr|Dra|Lic|Ing|Arq|Prof)\.\s*/u', '', $names));
        $letter = fn (string $word) => mb_strtoupper(mb_substr(trim($word), 0, 1));
        $people = preg_split('/\s+(?:y|&|e)\s+/u', $clean) ?: [];

        if (count($people) > 1) {
            return collect($people)->take(2)->map($letter)->implode('');
        }

        $words = preg_split('/\s+/u', $clean) ?: [];

        return collect([$words[0] ?? '', count($words) > 1 ? end($words) : ''])->filter()->map($letter)->implode('');
    };
    $padrinos = array_values(array_filter($data['padrinos'] ?? [], fn ($padrino) => !empty($padrino['nombres'] ?? null)));
    $groups = array_filter([
        ($invCopy['court_men'] ?? 'Familia') => array_values(array_filter(array_map($normalizePerson, $data['chambelanes'] ?? []), fn ($person) => $person['nombre'] !== '')),
        ($invCopy['court_women'] ?? 'Compañeros') => array_values(array_filter(array_map($normalizePerson, $data['damitas'] ?? []), fn ($person) => $person['nombre'] !== '')),
    ]);
@endphp

<section class="inv-section reveal br-medals-section" id="destacados">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['court_eyebrow'] ?? 'Quienes me acompañaron',
            'title' => $invCopy['court_title'] ?? 'Gracias a ustedes',
            'intro' => $invCopy['court_intro'] ?? 'Las personas que hicieron posible este logro.',
        ])

        @if(count($padrinos) || count($groups))
            @if(count($padrinos))
                <ul class="br-medals">
                    @foreach($padrinos as $index => $padrino)
                        <li class="br-medal" data-step style="--step: {{ $index }}">
                            <span class="br-medal__piece" aria-hidden="true">
                                <span class="br-medal__ribbon"></span>
                                <span class="br-medal__disc"><span>{{ $initials($padrino['nombres']) }}</span></span>
                            </span>
                            @if(!empty($padrino['rol']))
                                <span class="br-medal__role">{{ $padrino['rol'] }}</span>
                            @endif
                            <span class="br-medal__names">{{ $padrino['nombres'] }}</span>
                            @if(!empty($padrino['mensaje']))
                                <p class="br-medal__message">{{ $padrino['mensaje'] }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif

            @foreach($groups as $label => $people)
                <div class="br-group">
                    <h3 class="br-group__title">{{ $label }}</h3>
                    <ul class="br-group__list">
                        @foreach($people as $person)
                            <li class="br-group__person">
                                <span class="br-medal__disc br-medal__disc--small" aria-hidden="true"><span>{{ $initials($person['nombre']) }}</span></span>
                                <span class="br-group__name">{{ $person['nombre'] }}</span>
                                @if(!empty($person['detalle']))
                                    <small>{{ $person['detalle'] }}</small>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        @else
            <p class="inv-empty">{{ $invCopy['court_empty'] ?? 'Pronto presentaré a quienes me acompañaron.' }}</p>
        @endif
    </div>
</section>
