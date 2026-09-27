{{--
    Padrinos de «Móvil de cuna»: los padrinos cuelgan de una misma varilla de madera, de a dos, en
    equilibrio: son los que sostienen al bebé en la fe y en la vida. Cada tarjeta de fieltro lleva el
    papel (de bautizo, de vela, de ropón…), los nombres y su mensaje. Debajo cuelga la familia, en
    tarjetitas más chicas. Recibe $data (módulo destacados).
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

<section class="inv-section reveal mv-godparents" id="destacados">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['court_eyebrow'] ?? 'Quienes me guiarán',
            'title' => $invCopy['court_title'] ?? 'Mis padrinos',
            'intro' => $invCopy['court_intro'] ?? 'Las personas que me acompañarán en la fe y en la vida.',
        ])

        @if(count($padrinos) || count($family))
            @foreach(array_chunk($padrinos, 2) as $pair)
                {{-- Una varilla por cada par: los dos cuelgan juntos, en equilibrio --}}
                <div class="mv-balance {{ count($pair) === 1 ? 'is-single' : '' }}" data-step style="--step: {{ $loop->index }}">
                    <span class="mv-balance__bar" aria-hidden="true"></span>
                    <ul class="mv-balance__pair">
                        @foreach($pair as $padrino)
                            <li class="mv-card mv-godparent">
                                @if(!empty($padrino['rol']))
                                    <span class="mv-godparent__role">{{ $padrino['rol'] }}</span>
                                @endif
                                <span class="mv-godparent__names">{{ $padrino['nombres'] }}</span>
                                @if(!empty($padrino['mensaje']))
                                    <span class="mv-godparent__message">{{ $padrino['mensaje'] }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach

            @foreach($family as $label => $people)
                <div class="mv-family">
                    <h3 class="mv-family__title">{{ $label }}</h3>
                    <ul class="mv-family__tags">
                        @foreach($people as $person)
                            <li class="mv-card mv-family__tag">
                                <span>{{ $person['nombre'] }}</span>
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
