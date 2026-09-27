{{--
    Padrinos de «La gota»: cada par de padrinos es una gota que cayó al agua y abrió sus anillos; los
    anillos de uno tocan los del siguiente, como gotas que caen cerca. En el centro, el papel y los
    nombres; debajo, su mensaje. La familia sigue en ondas más chicas. Recibe $data (módulo destacados).
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
@endphp

<section class="inv-section reveal gt-ripples-section" id="destacados">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['court_eyebrow'] ?? 'Quienes me acompañan',
            'title' => $invCopy['court_title'] ?? 'Mis padrinos',
            'intro' => $invCopy['court_intro'] ?? 'Las personas que estarán cerca de mí mientras crezco.',
        ])

        @if(count($padrinos) || count($family))
            @if(count($padrinos))
                <ul class="gt-ripples">
                    @foreach($padrinos as $index => $padrino)
                        <li class="gt-ripple" data-step style="--step: {{ $index }}">
                            <span class="gt-ripple__rings" aria-hidden="true"><i></i><i></i><i></i></span>
                            <div class="gt-ripple__center">
                                @if(!empty($padrino['rol']))
                                    <span class="gt-ripple__role">{{ $padrino['rol'] }}</span>
                                @endif
                                <span class="gt-ripple__names">{{ $padrino['nombres'] }}</span>
                            </div>
                            @if(!empty($padrino['mensaje']))
                                <p class="gt-ripple__message">{{ $padrino['mensaje'] }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif

            @foreach($family as $label => $people)
                <div class="gt-family">
                    <h3 class="gt-family__title">{{ $label }}</h3>
                    <ul class="gt-family__list">
                        @foreach($people as $person)
                            <li>
                                <span class="gt-family__name">{{ $person['nombre'] }}</span>
                                @if(!empty($person['detalle']))
                                    <small>{{ $person['detalle'] }}</small>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        @else
            <p class="inv-empty">{{ $invCopy['court_empty'] ?? 'Pronto presentaremos a mis padrinos.' }}</p>
        @endif
    </div>
</section>
