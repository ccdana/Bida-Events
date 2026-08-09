@php
    $sugerencias = $dressCode['sugerencias'] ?? [];
    $totalSug    = count($sugerencias);
    $colores     = $dressCode['colores_permitidos'] ?? [];
    $evitar      = $dressCode['evitar'] ?? [];
@endphp

<section class="invitation-section reveal invitation-dress-code" id="dress-code"
    x-data="{
        activeTab: 'sugerencias',
        openSugs: {},
        toggleSug(i) {
            this.openSugs[i] = !this.openSugs[i];
        },
        isOpenSug(i) {
            return !!this.openSugs[i];
        }
    }">
    <div class="section-inner-wide">
        <div class="invitation-dress-code__shell">
            @include('invitations.partials.icon', ['name' => 'shirt', 'class' => 'w-7 h-7 invitation-dress-code__icon'])
            <p class="invitation-dress-code__eyebrow">Vestimenta</p>
            <h2 class="invitation-dress-code__title">{{ $dressCode['titulo'] ?? 'Dress Code' }}</h2>
            <div class="invitation-dress-code__rule" aria-hidden="true"></div>

            @if(!empty($dressCode['descripcion']))
                <p class="invitation-dress-code__description">{{ $dressCode['descripcion'] }}</p>
            @endif
        </div>

        {{-- Tabs --}}
        <nav class="invitation-dress-code__tabs" role="tablist">
            @foreach(['sugerencias' => 'Sugerencias', 'colores' => 'Colores', 'evitar' => 'Evitar'] as $key => $label)
                <button type="button" role="tab" @click="activeTab = '{{ $key }}'"
                    class="invitation-dress-code__tab"
                    :class="activeTab === '{{ $key }}' ? 'is-active' : ''"
                    :aria-selected="activeTab === '{{ $key }}'">
                    {{ $label }}
                </button>
            @endforeach
        </nav>

        {{-- Tab: Sugerencias --}}
        <div x-show="activeTab === 'sugerencias'" x-cloak class="invitation-dress-code__panel">
            @if($totalSug === 1)
                {{-- 1 sola sugerencia: NO es desplegable, se muestra completa --}}
                @foreach($sugerencias as $sug)
                    <article class="invitation-dress-code__suggestion">
                        <div class="invitation-dress-code__suggestion-media">
                            @if(!empty($sug['imagen']))
                                <img src="{{ $sug['imagen'] }}" alt="{{ $sug['titulo'] ?? 'Vestimenta' }}" loading="lazy">
                            @else
                                <div class="invitation-dress-code__suggestion-media-placeholder">
                                    @include('invitations.partials.icon', ['name' => 'shirt', 'class' => 'w-10 h-10'])
                                </div>
                            @endif
                        </div>
                        <div class="invitation-dress-code__suggestion-body">
                            @if(!empty($sug['para']))
                                <p class="invitation-dress-code__suggestion-label">{{ $sug['para'] }}</p>
                            @endif
                            <h3 class="invitation-dress-code__suggestion-title">{{ $sug['titulo'] }}</h3>
                            @if(!empty($sug['descripcion']))
                                <p class="invitation-dress-code__suggestion-text">{{ $sug['descripcion'] }}</p>
                            @endif
                            @if(!empty($sug['ejemplos']))
                                <ul class="invitation-dress-code__suggestion-list">
                                    @foreach($sug['ejemplos'] as $ej)
                                        <li>{{ $ej }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </article>
                @endforeach
            @elseif($totalSug > 1)
                {{-- Más de 1 sugerencia: Cada sugerencia es desplegable de forma independiente (cerradas por defecto) --}}
                <div class="w-full max-w-2xl mx-auto space-y-3">
                    @foreach($sugerencias as $i => $sug)
                        <div class="invitation-dress-code__accordion">
                            <button type="button" @click="toggleSug({{ $i }})"
                                class="invitation-dress-code__accordion-trigger"
                                :class="isOpenSug({{ $i }}) ? 'is-active' : ''">
                                <div>
                                    @if(!empty($sug['para']))
                                        <span class="text-[0.65rem] uppercase tracking-widest block opacity-70 mb-0.5">{{ $sug['para'] }}</span>
                                    @endif
                                    <span class="font-medium text-sm">{{ $sug['titulo'] }}</span>
                                </div>
                                <svg class="w-4 h-4 transition-transform duration-300 shrink-0 ml-2" :class="isOpenSug({{ $i }}) ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="isOpenSug({{ $i }})" x-cloak class="invitation-dress-code__accordion-body">
                                @if(!empty($sug['imagen']))
                                    <div class="invitation-dress-code__suggestion-media mb-4">
                                        <img src="{{ $sug['imagen'] }}" alt="{{ $sug['titulo'] ?? 'Vestimenta' }}" loading="lazy">
                                    </div>
                                @endif
                                <div class="invitation-dress-code__suggestion-body p-0">
                                    @if(!empty($sug['descripcion']))
                                        <p class="invitation-dress-code__suggestion-text mt-0">{{ $sug['descripcion'] }}</p>
                                    @endif
                                    @if(!empty($sug['ejemplos']))
                                        <ul class="invitation-dress-code__suggestion-list">
                                            @foreach($sug['ejemplos'] as $ej)
                                                <li>{{ $ej }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="invitation-dress-code__empty">Consulta con los anfitriones sobre la vestimenta ideal.</p>
            @endif
        </div>

        {{-- Tab: Colores --}}
        <div x-show="activeTab === 'colores'" x-cloak class="invitation-dress-code__panel">
            @if(!empty($colores))
                <p class="invitation-dress-code__palette-hint">Paleta recomendada para la noche</p>
                <div class="invitation-dress-code__palette">
                    @foreach($colores as $color)
                        <div class="invitation-dress-code__color">
                            <span class="invitation-dress-code__color-circle" style="background: {{ $color['hex'] }};"></span>
                            <span class="invitation-dress-code__color-name">{{ $color['nombre'] }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="invitation-dress-code__empty">Aún no hay colores definidos.</p>
            @endif
        </div>

        {{-- Tab: Evitar --}}
        <div x-show="activeTab === 'evitar'" x-cloak class="invitation-dress-code__panel">
            @if(!empty($evitar))
                <div class="invitation-dress-code__avoid-list">
                    @foreach($evitar as $i => $item)
                        @php $text = is_string($item) ? $item : ($item['motivo'] ?? $item['nombre'] ?? ''); @endphp
                        @if(!empty($text))
                            <div class="invitation-dress-code__avoid-item">
                                <span class="invitation-dress-code__avoid-number">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}.</span>
                                <span class="invitation-dress-code__avoid-text">{{ $text }}</span>
                            </div>
                        @endif
                    @endforeach
                </div>
            @else
                <p class="invitation-dress-code__empty">No hay restricciones de vestimenta.</p>
            @endif
        </div>
    </div>
</section>
