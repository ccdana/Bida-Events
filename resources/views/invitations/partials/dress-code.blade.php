<section class="invitation-section reveal invitation-dress-code" id="dress-code" x-data="{ tab: 'sugerencias' }">
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
                <button type="button" role="tab" @click="tab='{{ $key }}'"
                    class="invitation-dress-code__tab"
                    :class="tab === '{{ $key }}' ? 'is-active' : ''"
                    :aria-selected="tab === '{{ $key }}'">
                    {{ $label }}
                </button>
            @endforeach
        </nav>

        {{-- Tab: Sugerencias --}}
        <div x-show="tab === 'sugerencias'" x-cloak class="invitation-dress-code__panel">
            @forelse($dressCode['sugerencias'] ?? [] as $i => $sug)
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
            @empty
                <p class="invitation-dress-code__empty">Consulta con los anfitriones sobre la vestimenta ideal.</p>
            @endforelse
        </div>

        {{-- Tab: Colores --}}
        <div x-show="tab === 'colores'" x-cloak class="invitation-dress-code__panel">
            @if(!empty($dressCode['colores_permitidos']))
                <p class="invitation-dress-code__palette-hint">Paleta recomendada para la noche</p>
                <div class="invitation-dress-code__palette">
                    @foreach($dressCode['colores_permitidos'] ?? [] as $color)
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
        <div x-show="tab === 'evitar'" x-cloak class="invitation-dress-code__panel">
            @if(!empty($dressCode['evitar']))
                <div class="invitation-dress-code__avoid-list">
                    @foreach($dressCode['evitar'] ?? [] as $i => $item)
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
