@php
    $sugerencias = array_values(array_filter($dressCode['sugerencias'] ?? [], fn ($sug) => !empty($sug['titulo'] ?? null)));
    $colores = array_values(array_filter($dressCode['colores_permitidos'] ?? [], fn ($color) => preg_match('/^#[0-9a-f]{3,8}$/i', $color['hex'] ?? '')));
    $evitar = collect($dressCode['evitar'] ?? [])
        ->map(fn ($item) => is_string($item) ? $item : ($item['motivo'] ?? $item['nombre'] ?? ''))
        ->filter()
        ->values();
    $tabs = array_filter([
        'sugerencias' => count($sugerencias) ? 'Qué usar' : null,
        'colores' => count($colores) ? 'Colores' : null,
        'evitar' => $evitar->isNotEmpty() ? 'Evitar' : null,
    ]);
@endphp

<section class="inv-section reveal inv-dress" id="dress-code" x-data="{ tab: @js(array_key_first($tabs) ?? '') }">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'lottie' => 'dress',
            'eyebrow' => 'Vestimenta',
            'title' => $dressCode['titulo'] ?? 'Dress code',
            'intro' => $dressCode['descripcion'] ?? null,
        ])

        @if(!empty($dressCode['estilo']))
            <p class="inv-dress__style">{{ $dressCode['estilo'] }}</p>
        @endif

        @if(count($tabs) > 1)
            <div class="inv-tabs" role="tablist" aria-label="Detalles de vestimenta">
                @foreach($tabs as $key => $label)
                    <button type="button" role="tab" class="inv-tab"
                        :class="{ 'is-active': tab === '{{ $key }}' }"
                        :aria-selected="(tab === '{{ $key }}').toString()"
                        @click="tab = '{{ $key }}'">{{ $label }}</button>
                @endforeach
            </div>
        @endif

        @if(isset($tabs['sugerencias']))
            {{-- Cada sugerencia es desplegable: cerrada solo muestra miniatura y título --}}
            <div class="inv-folds" x-show="tab === 'sugerencias'" role="tabpanel">
                <p class="inv-help inv-folds__hint">Toca cada opción para ver el detalle</p>
                @foreach($sugerencias as $sug)
                    <details class="inv-fold inv-dress__fold" name="dress-code-sugerencias">
                        <summary class="inv-fold__summary">
                            @if(!empty($sug['imagen']))
                                <img src="{{ \App\Support\CloudinaryImage::url($sug['imagen'], 120) }}"
                                    alt="" width="56" height="56" class="inv-dress__thumb" loading="lazy" decoding="async">
                            @endif
                            <span class="inv-fold__heading">
                                @if(!empty($sug['para']))
                                    <span class="inv-label">{{ $sug['para'] }}</span>
                                @endif
                                <span class="inv-dress__title">{{ $sug['titulo'] }}</span>
                            </span>
                            <span class="inv-fold__chevron" aria-hidden="true"></span>
                        </summary>

                        <div class="inv-fold__body inv-dress__item {{ !empty($sug['imagen']) ? 'has-image' : '' }}">
                            @if(!empty($sug['imagen']))
                                @php($dressSrcset = \App\Support\CloudinaryImage::srcset($sug['imagen'], [400, 800]))
                                <img src="{{ \App\Support\CloudinaryImage::url($sug['imagen'], 800) }}"
                                    @if($dressSrcset) srcset="{{ $dressSrcset }}" sizes="(min-width: 640px) 13rem, 100vw" @endif
                                    alt="{{ $sug['titulo'] }}" width="800" height="1000"
                                    class="inv-dress__image" loading="lazy" decoding="async">
                            @endif
                            @if(!empty($sug['descripcion']) || !empty($sug['ejemplos']))
                                <div>
                                    @if(!empty($sug['descripcion']))
                                        <p class="inv-dress__text">{{ $sug['descripcion'] }}</p>
                                    @endif
                                    @if(!empty($sug['ejemplos']))
                                        <p class="inv-dress__examples">Ideas: {{ implode(' · ', $sug['ejemplos']) }}</p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </details>
                @endforeach
            </div>
        @endif

        @if(isset($tabs['colores']))
            <div x-show="tab === 'colores'" x-cloak role="tabpanel">
                <p class="inv-help inv-dress__hint">Tonos sugeridos para la noche</p>
                <ul class="inv-dress__palette">
                    @foreach($colores as $color)
                        <li>
                            <span class="inv-dress__swatch" style="background: {{ $color['hex'] }}" aria-hidden="true"></span>
                            <span class="inv-dress__swatch-name">{{ $color['nombre'] ?? $color['hex'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(isset($tabs['evitar']))
            <ul class="inv-list" x-show="tab === 'evitar'" x-cloak role="tabpanel">
                @foreach($evitar as $item)
                    <li class="inv-dress__avoid">
                        @include('invitations.partials.icon', ['name' => 'close', 'animated' => false])
                        <span>{{ $item }}</span>
                    </li>
                @endforeach
            </ul>
        @endif

        @if(empty($tabs))
            <p class="inv-empty">Viste elegante y cómodo para disfrutar toda la noche.</p>
        @endif
    </div>
</section>
