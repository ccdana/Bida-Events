{{--
    Padrinos de «Entre nubes»: cada par de padrinos en su propia nube, con el papel arriba y los nombres
    en el medio; su mensaje queda debajo, como una estela. Las nubes flotan a destiempo. La familia va
    en nubecitas más chicas. Recibe $data (módulo destacados).
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

<section class="inv-section reveal nb-godparents" id="destacados">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['court_eyebrow'] ?? 'Quienes me guiarán',
            'title' => $invCopy['court_title'] ?? 'Mis padrinos',
            'intro' => $invCopy['court_intro'] ?? 'Las personas que me acompañarán en la fe y en la vida.',
        ])

        @if(count($padrinos) || count($family))
            @if(count($padrinos))
                <ul class="nb-sponsors">
                    @foreach($padrinos as $index => $padrino)
                        <li class="nb-sponsor" data-step style="--step: {{ $index }}">
                            <div class="nb-sponsor__cloud">
                                @include('invitations.partials.nubes.cloud', ['class' => 'nb-sponsor__shape', 'shade' => true])
                                <div class="nb-sponsor__text">
                                    @if(!empty($padrino['rol']))
                                        <span class="nb-sponsor__role">{{ $padrino['rol'] }}</span>
                                    @endif
                                    <span class="nb-sponsor__names">{{ $padrino['nombres'] }}</span>
                                </div>
                            </div>
                            @if(!empty($padrino['mensaje']))
                                <p class="nb-sponsor__message">{{ $padrino['mensaje'] }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif

            @foreach($family as $label => $people)
                <div class="nb-family">
                    <h3 class="nb-family__title">{{ $label }}</h3>
                    <ul class="nb-family__list">
                        @foreach($people as $person)
                            <li>
                                <span class="nb-family__name">{{ $person['nombre'] }}</span>
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
