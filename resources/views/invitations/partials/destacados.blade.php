@php
    $normalizePerson = fn ($persona) => is_array($persona)
        ? ['nombre' => $persona['nombre'] ?? '', 'detalle' => $persona['detalle'] ?? null]
        : ['nombre' => (string) $persona, 'detalle' => null];
    $groups = array_filter([
        'Chambelanes' => array_values(array_filter(array_map($normalizePerson, $destacados['chambelanes'] ?? []), fn ($p) => $p['nombre'] !== '')),
        'Damitas' => array_values(array_filter(array_map($normalizePerson, $destacados['damitas'] ?? []), fn ($p) => $p['nombre'] !== '')),
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
            'lottie' => 'crown',
            'eyebrow' => 'Quienes me acompañan',
            'title' => 'Mi cortejo',
            'intro' => 'Personas muy especiales que estarán a mi lado esta noche.',
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
            <div x-show="tab === 'cortejo'" role="tabpanel">
                @foreach($groups as $groupLabel => $people)
                    <div class="inv-court__group">
                        <h3 class="inv-court__group-title">
                            {{ $groupLabel }}
                            <span class="inv-court__count">{{ count($people) }}</span>
                        </h3>
                        <ul class="inv-court__names">
                            @foreach($people as $person)
                                <li>
                                    <span class="inv-court__name">{{ $person['nombre'] }}</span>
                                    @if(!empty($person['detalle']))
                                        <span class="inv-court__detail">{{ $person['detalle'] }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        @endif

        @if(isset($tabs['padrinos']))
            <ul class="inv-list" x-show="tab === 'padrinos'" x-cloak role="tabpanel">
                @foreach($padrinos as $padrino)
                    <li>
                        @if(!empty($padrino['rol']))
                            <span class="inv-label">{{ $padrino['rol'] }}</span>
                        @endif
                        <p class="inv-sponsor__names">{{ $padrino['nombres'] }}</p>
                        @if(!empty($padrino['mensaje']))
                            <p class="inv-sponsor__message">{{ $padrino['mensaje'] }}</p>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif

        @if(empty($tabs))
            <p class="inv-empty">Pronto presentaremos a quienes me acompañan.</p>
        @endif
    </div>
</section>
