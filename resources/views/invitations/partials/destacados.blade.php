@php
    $normalizePerson = fn ($persona) => is_array($persona)
        ? ['nombre' => $persona['nombre'] ?? '', 'detalle' => $persona['detalle'] ?? null]
        : ['nombre' => (string) $persona, 'detalle' => null];
    $groups = array_filter([
        ($invCopy['court_men'] ?? 'Chambelanes') => array_values(array_filter(array_map($normalizePerson, $destacados['chambelanes'] ?? []), fn ($p) => $p['nombre'] !== '')),
        ($invCopy['court_women'] ?? 'Damitas') => array_values(array_filter(array_map($normalizePerson, $destacados['damitas'] ?? []), fn ($p) => $p['nombre'] !== '')),
    ]);
    $padrinos = array_values(array_filter($destacados['padrinos'] ?? [], fn ($p) => !empty($p['nombres'] ?? null)));
    $tabs = array_filter([
        'cortejo' => count($groups) ? 'Cortejo' : null,
        'padrinos' => count($padrinos) ? 'Padrinos' : null,
    ]);
@endphp

<section class="inv-section reveal inv-court" id="destacados" x-data="{ tab: @js(array_key_first($tabs) ?? '') }">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'lottie' => $invCopy['court_lottie'] ?? 'crown',
            'eyebrow' => $invCopy['court_eyebrow'] ?? 'Quienes me acompañan',
            'title' => $invCopy['court_title'] ?? 'Mi cortejo',
            'intro' => $invCopy['court_intro'] ?? 'Personas muy especiales que estarán a mi lado esta noche.',
        ])

        @if(count($tabs) > 1)
            <div class="inv-tabs" role="tablist" aria-label="Cortejo y padrinos">
                @foreach($tabs as $key => $label)
                    <button type="button" role="tab" class="inv-tab"
                        :class="{ 'is-active': tab === '{{ $key }}' }"
                        :aria-selected="(tab === '{{ $key }}').toString()"
                        @click="tab = '{{ $key }}'">{{ $label }}</button>
                @endforeach
            </div>
        @endif

        @if(isset($tabs['cortejo']))
            {{-- Cada grupo se despliega al tocarlo; cerrado muestra nombre del grupo y cantidad --}}
            <div class="inv-folds" x-show="tab === 'cortejo'" role="tabpanel">
                <p class="inv-help inv-folds__hint">Toca cada grupo para ver los nombres</p>
                @foreach($groups as $groupLabel => $people)
                    <details class="inv-fold inv-court__group">
                        <summary class="inv-fold__summary">
                            <span class="inv-fold__heading">
                                <span class="inv-court__group-title">{{ $groupLabel }}</span>
                            </span>
                            <span class="inv-court__count">{{ count($people) }} {{ count($people) === 1 ? 'persona' : 'personas' }}</span>
                            <span class="inv-fold__chevron" aria-hidden="true"></span>
                        </summary>
                        <ul class="inv-fold__body inv-court__names">
                            @foreach($people as $person)
                                <li>
                                    <span class="inv-court__name">{{ $person['nombre'] }}</span>
                                    @if(!empty($person['detalle']))
                                        <span class="inv-court__detail">{{ $person['detalle'] }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </details>
                @endforeach
            </div>
        @endif

        @if(isset($tabs['padrinos']))
            {{-- Los nombres siempre visibles; el mensaje de cada padrino se despliega --}}
            <div class="inv-folds" x-show="tab === 'padrinos'" x-cloak role="tabpanel">
                @foreach($padrinos as $padrino)
                    @if(!empty($padrino['mensaje']))
                        <details class="inv-fold" name="cortejo-padrinos">
                            <summary class="inv-fold__summary">
                                <span class="inv-fold__heading">
                                    @if(!empty($padrino['rol']))
                                        <span class="inv-label">{{ $padrino['rol'] }}</span>
                                    @endif
                                    <span class="inv-sponsor__names">{{ $padrino['nombres'] }}</span>
                                </span>
                                <span class="inv-fold__chevron" aria-hidden="true"></span>
                            </summary>
                            <div class="inv-fold__body">
                                <p class="inv-sponsor__message">{{ $padrino['mensaje'] }}</p>
                            </div>
                        </details>
                    @else
                        <div class="inv-fold inv-fold--static">
                            <span class="inv-fold__heading">
                                @if(!empty($padrino['rol']))
                                    <span class="inv-label">{{ $padrino['rol'] }}</span>
                                @endif
                                <span class="inv-sponsor__names">{{ $padrino['nombres'] }}</span>
                            </span>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif

        @if(empty($tabs))
            <p class="inv-empty">{{ $invCopy['court_empty'] ?? 'Pronto presentaremos a quienes me acompañan.' }}</p>
        @endif
    </div>
</section>
