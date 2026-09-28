{{--
    Anfitrionas de «Encomienda especial»: las remitentes, cada una en su estampilla (el borde picado,
    la inicial, el año y el matasellos encima), con su papel, sus nombres y su mensaje. La familia va
    como la lista de entrega, con su casilla marcada. Recibe $data (módulo destacados).
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
    $initialOf = function (string $names): string {
        $words = array_values(array_filter(preg_split('/\s+/u', trim($names)) ?: [], fn ($word) => ! preg_match('/^(sr|sra|srta|dr|dra|don|doña|lic|ing)\.?$/iu', $word)));

        return mb_strtoupper(mb_substr($words[0] ?? $names, 0, 1));
    };
@endphp

<section class="inv-section reveal en-senders-section" id="destacados">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['court_eyebrow'] ?? 'Remitentes',
            'title' => $invCopy['court_title'] ?? 'Con mucho cariño',
            'intro' => $invCopy['court_intro'] ?? null,
        ])

        @if(count($padrinos) || count($family))
            @if(count($padrinos))
                <ul class="en-senders">
                    @foreach($padrinos as $index => $padrino)
                        <li class="en-sender" data-step style="--step: {{ $index }}">
                            <span class="en-postage" aria-hidden="true">
                                <span class="en-postage__art">
                                    <b>{{ $initialOf($padrino['nombres']) }}</b>
                                    <small>{{ $page->eventDate->format('Y') }}</small>
                                </span>
                                <span class="en-postage__cancel"></span>
                            </span>
                            @if(!empty($padrino['rol']))
                                <span class="en-sender__role">{{ $padrino['rol'] }}</span>
                            @endif
                            <span class="en-sender__names">{{ $padrino['nombres'] }}</span>
                            @if(!empty($padrino['mensaje']))
                                <p class="en-sender__message">{{ $padrino['mensaje'] }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif

            @if(count($family))
                <div class="en-family">
                    @foreach($family as $label => $people)
                        <div class="en-family__group">
                            <h3 class="en-family__title">{{ $label }}</h3>
                            <ul class="en-family__list">
                                @foreach($people as $person)
                                    <li>
                                        <span class="en-family__name">{{ $person['nombre'] }}</span>
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
