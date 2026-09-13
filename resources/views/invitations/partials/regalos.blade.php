@php
    $regalos = $regalos ?? [];
    $banco = is_array($regalos['banco'] ?? null) ? $regalos['banco'] : [];
    $sobres = is_array($regalos['sobres'] ?? null) ? $regalos['sobres'] : [];
    $opciones = array_values(array_filter(is_array($regalos['opciones'] ?? null) ? $regalos['opciones'] : [], fn ($gift) => !empty($gift['titulo'] ?? null)));

    $bankFields = array_filter(
        ['banco' => 'Banco', 'titular' => 'Titular', 'ci' => 'Documento de identidad', 'cuenta' => 'Número de cuenta'],
        fn ($label, $key) => !empty($banco[$key] ?? null),
        ARRAY_FILTER_USE_BOTH
    );
    $hasBanco = count($bankFields) > 0 || !empty($banco['qr_url']);
    $hasSobres = !empty($sobres['titulo']) || !empty($sobres['direccion']);
    $hasTienda = !empty($regalos['tienda_url']);
    $hasAnyGiftContent = $hasBanco || $hasSobres || $hasTienda || count($opciones) > 0;
@endphp

@if($hasAnyGiftContent)
<section class="inv-section reveal inv-gifts" id="regalos" x-data="{ showBank: false }"
    x-effect="document.documentElement.classList.toggle('inv-lock', showBank)">
    <div class="inv-wrap">
        @include('invitations.partials.section-header', [
            'lottie' => 'gift',
            'eyebrow' => 'Detalles especiales',
            'title' => $regalos['titulo'] ?? 'Regalos',
            'intro' => 'Tu presencia es mi mejor regalo. Si deseas tener un detalle, aquí tienes algunas opciones.',
        ])

        <ul class="inv-list">
            @if($hasBanco)
                <li>
                    <button type="button" class="inv-gift" @click="showBank = true">
                        <span class="inv-gift__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 10l9-6 9 6M5 10v8M9.5 10v8M14.5 10v8M19 10v8M3 20h18" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <span class="inv-gift__body">
                            <span class="inv-gift__title">Transferencia bancaria</span>
                            <span class="inv-gift__text">Ver datos de la cuenta{{ !empty($banco['qr_url']) ? ' y código QR' : '' }}</span>
                        </span>
                        <span class="inv-gift__chevron" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 5l7 7-7 7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                    </button>
                </li>
            @endif

            @if($hasTienda)
                <li>
                    <a href="{{ $regalos['tienda_url'] }}" target="_blank" rel="noopener" class="inv-gift">
                        <span class="inv-gift__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 8h12l-1 12H7L6 8zM9 8a3 3 0 016 0" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <span class="inv-gift__body">
                            <span class="inv-gift__title">{{ $regalos['tienda_texto'] ?? 'Mesa de regalos' }}</span>
                            <span class="inv-gift__text">Se abre en una pestaña nueva</span>
                        </span>
                        <span class="inv-gift__chevron" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 16L16 8M9 8h7v7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                    </a>
                </li>
            @endif

            @foreach($opciones as $gift)
                <li>
                    @if(!empty($gift['enlace']))
                        <a href="{{ $gift['enlace'] }}" target="_blank" rel="noopener" class="inv-gift">
                    @else
                        <div class="inv-gift">
                    @endif
                        <span class="inv-gift__icon" aria-hidden="true">
                            @include('invitations.partials.icon', ['name' => $gift['icono'] ?? 'gift', 'animated' => false])
                        </span>
                        <span class="inv-gift__body">
                            <span class="inv-gift__title">{{ $gift['titulo'] }}</span>
                            @if(!empty($gift['descripcion']))
                                <span class="inv-gift__text">{{ $gift['descripcion'] }}</span>
                            @endif
                        </span>
                    @if(!empty($gift['enlace']))
                            <span class="inv-gift__chevron" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 16L16 8M9 8h7v7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                        </a>
                    @else
                        </div>
                    @endif
                </li>
            @endforeach

            @if($hasSobres)
                <li>
                    <div class="inv-gift">
                        <span class="inv-gift__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 6h16v12H4zM4 7l8 6 8-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <span class="inv-gift__body">
                            <span class="inv-gift__title">{{ $sobres['titulo'] ?? 'Lluvia de sobres' }}</span>
                            @if(!empty($sobres['direccion']))
                                <span class="inv-gift__text">{{ $sobres['direccion'] }}</span>
                            @endif
                        </span>
                    </div>
                </li>
            @endif
        </ul>
    </div>

    @if($hasBanco)
    <template x-teleport="body">
        <div class="inv-page inv-sheet-backdrop" x-show="showBank" x-cloak
            x-transition.opacity.duration.250ms
            @click.self="showBank = false"
            @keydown.escape.window="showBank = false">
            <div class="inv-sheet" role="dialog" aria-modal="true" aria-labelledby="bank-sheet-title"
                x-data="copyButton()"
                x-show="showBank"
                x-transition:enter="inv-sheet-anim"
                x-transition:enter-start="inv-sheet-hidden"
                x-transition:enter-end="inv-sheet-shown"
                x-transition:leave="inv-sheet-anim"
                x-transition:leave-start="inv-sheet-shown"
                x-transition:leave-end="inv-sheet-hidden">
                <div class="inv-sheet__grip" aria-hidden="true"></div>
                <div class="inv-sheet__header">
                    <h3 id="bank-sheet-title" class="inv-sheet__title">Transferencia bancaria</h3>
                    <button type="button" class="inv-sheet__close" @click="showBank = false" aria-label="Cerrar">
                        @include('invitations.partials.icon', ['name' => 'close', 'animated' => false])
                    </button>
                </div>
                <p class="inv-sheet__intro">Toca «Copiar» y pega el dato en la app de tu banco.</p>

                @if(count($bankFields))
                    <dl class="inv-bank">
                        @foreach($bankFields as $key => $label)
                            <div class="inv-bank__row">
                                <div class="inv-bank__data">
                                    <dt class="inv-label">{{ $label }}</dt>
                                    <dd class="inv-bank__value">{{ $banco[$key] }}</dd>
                                </div>
                                <button type="button" class="inv-link" @click="copy(@js((string) $banco[$key]), @js($key))">
                                    <span x-text="copied === '{{ $key }}' ? 'Copiado' : 'Copiar'">Copiar</span>
                                </button>
                            </div>
                        @endforeach
                    </dl>
                @endif

                @if(!empty($banco['qr_url']))
                    <figure class="inv-bank__qr">
                        <img src="{{ $banco['qr_url'] }}" alt="Código QR para transferir" loading="lazy">
                        <figcaption class="inv-help">Escanea el código desde la app de tu banco</figcaption>
                    </figure>
                @endif

                <button type="button" class="inv-btn inv-btn--block inv-sheet__done" @click="showBank = false">Listo</button>
            </div>
        </div>
    </template>
    @endif
</section>
@endif
