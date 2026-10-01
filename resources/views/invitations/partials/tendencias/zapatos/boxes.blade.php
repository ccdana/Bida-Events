{{--
    Padrinos de «El cambio de zapatos»: la vitrina de la zapatería. Cada padrino es una caja apilada,
    vista por su costado, con la tapa encima y la etiqueta pegada: su papel como el modelo, sus nombres,
    su mensaje como la nota de la etiqueta, el código de barras y la talla. La corte va en dos columnas,
    cada nombre con su moño. Recibe $data (módulo destacados).
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

<section class="inv-section reveal zp-boxes-section" id="destacados">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['court_eyebrow'] ?? 'Quienes me acompañan',
            'title' => $invCopy['court_title'] ?? 'Mi corte de honor',
            'intro' => $invCopy['court_intro'] ?? null,
        ])

        @if(count($padrinos) || count($court))
            @if(count($padrinos))
                <ul class="zp-shelf">
                    @foreach($padrinos as $index => $padrino)
                        <li class="zp-shelf__box" data-step data-poke="pop" style="--step: {{ $index }}">
                            <span class="zp-shelf__lid" aria-hidden="true"></span>
                            <span class="zp-shelf__label">
                                @if(!empty($padrino['rol']))
                                    <span class="zp-shelf__role"><small>{{ $invCopy['shoe_model'] ?? 'Modelo' }}</small>{{ $padrino['rol'] }}</span>
                                @endif
                                <span class="zp-shelf__names">{{ $padrino['nombres'] }}</span>
                                @if(!empty($padrino['mensaje']))
                                    <span class="zp-shelf__note">{{ $padrino['mensaje'] }}</span>
                                @endif
                                <span class="zp-shelf__foot" aria-hidden="true">
                                    <span class="zp-label__bars"></span>
                                    <span class="zp-shelf__size"><small>{{ $invCopy['shoe_ref'] ?? 'Ref.' }}</small>{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                                </span>
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif

            @if(count($court))
                <div class="zp-court">
                    @foreach($court as $label => $people)
                        <div class="zp-court__group">
                            <h3 class="zp-court__title">{{ $label }}</h3>
                            <ul class="zp-court__list">
                                @foreach($people as $person)
                                    <li style="--i: {{ $loop->index }}">
                                        <span class="zp-court__name">{{ $person['nombre'] }}</span>
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
