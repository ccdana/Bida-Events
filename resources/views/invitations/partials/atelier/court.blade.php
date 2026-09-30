{{--
    Corte de «Atelier»: los padrinos son etiquetas colgadas del perchero del taller, cada una con su
    hilo, su papel, sus nombres y su mensaje. La corte de honor va como el orden de salida del
    desfile: damas y chambelanes, cada nombre con su número de look. Recibe $data (módulo destacados).
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
@endphp

<section class="inv-section reveal at-court-section" id="destacados">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['court_eyebrow'] ?? 'Quienes me acompañan',
            'title' => $invCopy['court_title'] ?? 'Mi corte de honor',
            'intro' => $invCopy['court_intro'] ?? null,
        ])

        @if(count($padrinos) || count($court))
            @if(count($padrinos))
                <div class="at-rack">
                    <span class="at-rack__bar" aria-hidden="true"></span>
                    <ul class="at-tags">
                        @foreach($padrinos as $index => $padrino)
                            <li class="at-tag" data-step style="--step: {{ $index }}">
                                <span class="at-tag__hole" aria-hidden="true"></span>
                                @if(!empty($padrino['rol']))
                                    <span class="at-tag__role">{{ $padrino['rol'] }}</span>
                                @endif
                                <span class="at-tag__names">{{ $padrino['nombres'] }}</span>
                                @if(!empty($padrino['mensaje']))
                                    <span class="at-tag__message">{{ $padrino['mensaje'] }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(count($court))
                <div class="at-lineup">
                    @foreach($court as $label => $people)
                        <div class="at-lineup__group">
                            <h3 class="at-lineup__title">{{ $label }}</h3>
                            <ol class="at-lineup__list">
                                @foreach($people as $person)
                                    <li>
                                        <span class="at-lineup__look">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                        <span class="at-lineup__name">{{ $person['nombre'] }}</span>
                                        @if(!empty($person['detalle']))
                                            <small>{{ $person['detalle'] }}</small>
                                        @endif
                                    </li>
                                @endforeach
                            </ol>
                        </div>
                    @endforeach
                </div>
            @endif
        @else
            <p class="inv-empty">{{ $invCopy['court_empty'] ?? 'Pronto presentaré a mi corte de honor.' }}</p>
        @endif
    </div>
</section>
