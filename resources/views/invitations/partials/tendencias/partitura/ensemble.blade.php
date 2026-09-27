{{--
    Padrinos y cortejo de «Partitura a dos voces»: la plantilla de instrumentos de la primera página
    de una partitura. Cada grupo (padrinos, caballeros, damas) va unido por una llave, como las
    familias de instrumentos, con el papel de cada uno en cursiva y su nombre. Todo a la vista, sin
    pestañas. Recibe $data (módulo destacados).
--}}
@php
    $normalizePerson = fn ($persona) => is_array($persona)
        ? ['nombre' => trim((string) ($persona['nombre'] ?? '')), 'detalle' => $persona['detalle'] ?? null]
        : ['nombre' => trim((string) $persona), 'detalle' => null];
    $padrinos = array_values(array_filter($data['padrinos'] ?? [], fn ($padrino) => !empty($padrino['nombres'] ?? null)));
    $sections = array_filter([
        ($invCopy['court_sponsors_tab'] ?? 'Padrinos') => array_map(fn ($padrino) => ['rol' => $padrino['rol'] ?? '', 'nombre' => $padrino['nombres'], 'mensaje' => $padrino['mensaje'] ?? null], $padrinos),
        ($invCopy['court_men'] ?? 'Caballeros de honor') => array_map(fn ($person) => ['rol' => $person['detalle'] ?? '', 'nombre' => $person['nombre'], 'mensaje' => null], array_values(array_filter(array_map($normalizePerson, $data['chambelanes'] ?? []), fn ($person) => $person['nombre'] !== ''))),
        ($invCopy['court_women'] ?? 'Damas de honor') => array_map(fn ($person) => ['rol' => $person['detalle'] ?? '', 'nombre' => $person['nombre'], 'mensaje' => null], array_values(array_filter(array_map($normalizePerson, $data['damitas'] ?? []), fn ($person) => $person['nombre'] !== ''))),
    ]);
@endphp

<section class="inv-section reveal pt-ensemble" id="destacados">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['court_eyebrow'] ?? 'El ensamble',
            'title' => $invCopy['court_title'] ?? 'Quienes tocan con nosotros',
            'intro' => $invCopy['court_intro'] ?? null,
        ])

        @if(count($sections))
            <div class="pt-orchestra">
                @foreach($sections as $label => $members)
                    <div class="pt-family" data-step style="--step: {{ $loop->index }}">
                        <h3 class="pt-family__name">{{ $label }}</h3>
                        <ul class="pt-family__list">
                            @foreach($members as $member)
                                <li class="pt-member">
                                    @if($member['rol'] !== '')
                                        <em class="pt-member__role">{{ $member['rol'] }}</em>
                                    @endif
                                    <span class="pt-member__name">{{ $member['nombre'] }}</span>
                                    @if(!empty($member['mensaje']))
                                        <span class="pt-member__message">{{ $member['mensaje'] }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        @else
            <p class="inv-empty">{{ $invCopy['court_empty'] ?? 'Pronto presentaremos a quienes nos acompañan.' }}</p>
        @endif
    </div>
</section>
