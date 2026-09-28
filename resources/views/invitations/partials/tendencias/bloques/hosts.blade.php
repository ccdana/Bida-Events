{{--
    Anfitrionas de «Bloques de juguete»: cada una (o cada pareja) con su bloque y la inicial tallada,
    su papel, sus nombres y su mensaje; los bloques se inclinan un poco, cada uno para su lado. La
    familia va en dos listas marcadas con cuadraditos de colores. Recibe $data (módulo destacados).
--}}
@php
    $normalizePerson = fn ($persona) => is_array($persona)
        ? ['nombre' => trim((string) ($persona['nombre'] ?? '')), 'detalle' => $persona['detalle'] ?? null]
        : ['nombre' => trim((string) $persona), 'detalle' => null];
    $padrinos = array_values(array_filter($data['padrinos'] ?? [], fn ($padrino) => !empty($padrino['nombres'] ?? null)));
    $family = array_filter([
        ($invCopy['court_men'] ?? 'Abuelos') => array_values(array_filter(array_map($normalizePerson, $data['chambelanes'] ?? []), fn ($person) => $person['nombre'] !== '')),
        ($invCopy['court_women'] ?? 'Tías y amigas') => array_values(array_filter(array_map($normalizePerson, $data['damitas'] ?? []), fn ($person) => $person['nombre'] !== '')),
    ]);
    // La inicial del primer nombre, sin los tratamientos (Sr., Sra., Dr.…)
    $initialOf = function (string $names): string {
        $words = array_values(array_filter(preg_split('/\s+/u', trim($names)) ?: [], fn ($word) => ! preg_match('/^(sr|sra|srta|dr|dra|don|doña|lic|ing)\.?$/iu', $word)));

        return mb_strtoupper(mb_substr($words[0] ?? $names, 0, 1));
    };
@endphp

<section class="inv-section reveal bl-hosts-section" id="destacados">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['court_eyebrow'] ?? 'Quienes lo organizan',
            'title' => $invCopy['court_title'] ?? 'Con mucho cariño',
            'intro' => $invCopy['court_intro'] ?? null,
        ])

        @if(count($padrinos) || count($family))
            @if(count($padrinos))
                <ul class="bl-hosts">
                    @foreach($padrinos as $index => $padrino)
                        <li class="bl-host" data-step style="--step: {{ $index }}">
                            @include('invitations.partials.tendencias.bloques.block', ['letter' => $initialOf($padrino['nombres']), 'tone' => $index % 3, 'class' => 'bl-host__block'])
                            @if(!empty($padrino['rol']))
                                <span class="bl-host__role">{{ $padrino['rol'] }}</span>
                            @endif
                            <span class="bl-host__names">{{ $padrino['nombres'] }}</span>
                            @if(!empty($padrino['mensaje']))
                                <p class="bl-host__message">{{ $padrino['mensaje'] }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif

            @if(count($family))
                <div class="bl-family">
                    @foreach($family as $label => $people)
                        <div class="bl-family__group">
                            <h3 class="bl-family__title">{{ $label }}</h3>
                            <ul class="bl-family__list">
                                @foreach($people as $person)
                                    <li class="bl-tone--{{ $loop->index % 3 }}">
                                        <span class="bl-family__name">{{ $person['nombre'] }}</span>
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
            <p class="inv-empty">{{ $invCopy['court_empty'] ?? 'Pronto presentaremos a quienes organizan la celebración.' }}</p>
        @endif
    </div>
</section>
