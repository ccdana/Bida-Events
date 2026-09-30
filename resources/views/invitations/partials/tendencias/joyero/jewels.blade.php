{{--
    Padrinos de «Joyero musical»: las joyas de la ceremonia. Cada padrino aparece con la joya que le
    toca por su papel (el anillo, la tiara, los aretes, la medalla, el collar o la pulsera; si su papel
    no es una joya, una gema), en un medallón de terciopelo con borde de oro, con su papel, sus nombres y
    su mensaje. La corte va en dos columnas, cada nombre con su gema. Recibe $data (módulo destacados).
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
    // La joya de cada padrino, por su papel en la ceremonia
    $jewelFor = function (?string $role): string {
        $role = mb_strtolower((string) $role);

        return match (true) {
            str_contains($role, 'anillo') => 'anillo',
            str_contains($role, 'corona') || str_contains($role, 'tiara') => 'tiara',
            str_contains($role, 'arete') || (bool) preg_match('/\baros?\b/u', $role) => 'aretes',
            str_contains($role, 'medalla') => 'medalla',
            str_contains($role, 'collar') || str_contains($role, 'cadena') => 'collar',
            str_contains($role, 'pulsera') || str_contains($role, 'esclava') => 'pulsera',
            default => 'gema',
        };
    };
@endphp

<section class="inv-section reveal jo-jewels-section" id="destacados">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'eyebrow' => $invCopy['court_eyebrow'] ?? 'Las joyas de la ceremonia',
            'title' => $invCopy['court_title'] ?? 'Mis padrinos y mi corte',
            'intro' => $invCopy['court_intro'] ?? null,
        ])

        @if(count($padrinos) || count($court))
            @if(count($padrinos))
                <ul class="jo-jewels">
                    @foreach($padrinos as $index => $padrino)
                        <li class="jo-jewel" data-step style="--step: {{ $index }}">
                            <span class="jo-jewel__medal" aria-hidden="true">
                                <svg class="jo-jewel__svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                                    @switch($jewelFor($padrino['rol'] ?? null))
                                        @case('anillo')
                                            <circle cx="12" cy="15" r="6"/>
                                            <path d="M9.5 6.5 L12 3.5 L14.5 6.5 L12 9 Z"/>
                                            @break
                                        @case('tiara')
                                            <path d="M3 17 L5 8 L9 12 L12 5 L15 12 L19 8 L21 17 Z"/>
                                            <path d="M3 20 H21"/>
                                            @break
                                        @case('aretes')
                                            <path d="M8 3.5 V7 M16 3.5 V7"/>
                                            <path d="M8 8 C5.5 11 5.5 15 8 17 C10.5 15 10.5 11 8 8 Z"/>
                                            <path d="M16 8 C13.5 11 13.5 15 16 17 C18.5 15 18.5 11 16 8 Z"/>
                                            @break
                                        @case('medalla')
                                            <path d="M8 3 L12 9 L16 3"/>
                                            <circle cx="12" cy="15" r="5.5"/>
                                            <path d="M12 12.4 L12.9 14.3 L15 14.5 L13.4 15.9 L13.9 18 L12 16.9 L10.1 18 L10.6 15.9 L9 14.5 L11.1 14.3 Z"/>
                                            @break
                                        @case('collar')
                                            <path d="M4 4 C4 11 8 15 12 15 C16 15 20 11 20 4"/>
                                            <path d="M12 15 L10 17.5 L12 21 L14 17.5 Z"/>
                                            @break
                                        @case('pulsera')
                                            <ellipse cx="12" cy="12" rx="8.5" ry="5.5"/>
                                            <ellipse cx="12" cy="12" rx="5.5" ry="3"/>
                                            @break
                                        @default
                                            <path d="M6 4 H18 L22 9 L12 21 L2 9 Z"/>
                                            <path d="M2 9 H22 M9 4 L7.5 9 L12 21 L16.5 9 L15 4"/>
                                    @endswitch
                                </svg>
                            </span>
                            @if(!empty($padrino['rol']))
                                <span class="jo-jewel__role">{{ $padrino['rol'] }}</span>
                            @endif
                            <span class="jo-jewel__names">{{ $padrino['nombres'] }}</span>
                            @if(!empty($padrino['mensaje']))
                                <span class="jo-jewel__message">{{ $padrino['mensaje'] }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif

            @if(count($court))
                <div class="jo-court">
                    @foreach($court as $label => $people)
                        <div class="jo-court__group">
                            <h3 class="jo-court__title">{{ $label }}</h3>
                            <ul class="jo-court__list">
                                @foreach($people as $person)
                                    <li>
                                        <span class="jo-court__name">{{ $person['nombre'] }}</span>
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
