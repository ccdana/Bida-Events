@php
    $regalos = $regalos ?? [];
    
    // Safely extract arrays
    $banco = is_array($regalos['banco'] ?? null) ? $regalos['banco'] : [];
    $sobres = is_array($regalos['sobres'] ?? null) ? $regalos['sobres'] : [];
    $opciones = is_array($regalos['opciones'] ?? null) ? $regalos['opciones'] : [];
    
    // Evaluate active status
    $hasBanco = !empty($banco['banco']) || !empty($banco['titular']) || !empty($banco['cuenta']) || !empty($banco['qr_url']) || !empty($banco['ci']);
    $hasSobres = !empty($sobres['titulo']) || !empty($sobres['direccion']);
    $hasTienda = !empty($regalos['tienda_url']);
    $hasOpciones = count($opciones) > 0;
    
    $hasAnyGiftContent = $hasBanco || $hasSobres || $hasTienda || $hasOpciones;
@endphp

@if($hasAnyGiftContent)
<section class="invitation-section reveal" x-data="{ showBank: false }"
    x-effect="document.body.style.overflow = showBank ? 'hidden' : ''">
    
    <div class="section-inner-wide">
        <header class="section-header">
            @include('invitations.partials.icon', ['name' => 'gift', 'class' => 'w-8 h-8 text-primary mx-auto mb-3'])
            <span class="section-eyebrow">Detalles especiales</span>
            <h2 class="section-title">{{ $regalos['titulo'] ?? 'Regalos' }}</h2>
            <div class="section-ornament"></div>
        </header>

        <div class="space-y-4">
            <!-- Principales: Banco y Tienda -->
            @if($hasBanco || $hasTienda)
            <div class="grid gap-4 {{ ($hasBanco && $hasTienda) ? 'sm:grid-cols-2' : 'sm:grid-cols-1 max-w-sm mx-auto' }}">
                <!-- Banco/Transferencia -->
                @if($hasBanco)
                <button type="button" @click="showBank=true" class="inv-card rounded-2xl p-6 text-left transition-all duration-300 hover:shadow-lg hover:scale-[1.02] active:scale-[0.98] group">
                    <div class="flex items-start justify-between mb-3">
                        <svg class="w-8 h-8 text-primary/60 group-hover:text-primary transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        <svg class="w-5 h-5 text-primary/40 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                    <p class="text-[11px] uppercase tracking-[0.2em] text-primary/60 font-semibold">Transferencia</p>
                    <p class="mt-2 font-title text-lg text-primary">Datos Bancarios</p>
                    <p class="mt-1 text-sm opacity-60">Realiza tu transferencia</p>
                </button>
                @endif

                <!-- Tienda de Regalos -->
                @if($hasTienda)
                    <a href="{{ $regalos['tienda_url'] }}" target="_blank" rel="noopener"
                        class="inv-card rounded-2xl p-6 text-left transition-all duration-300 hover:shadow-lg hover:scale-[1.02] active:scale-[0.98] group">
                        <div class="flex items-start justify-between mb-3">
                            <svg class="w-8 h-8 text-primary/60 group-hover:text-primary transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                            <svg class="w-5 h-5 text-primary/40 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                        <p class="text-[11px] uppercase tracking-[0.2em] text-primary/60 font-semibold">Online</p>
                        <p class="mt-2 font-title text-lg text-primary">{{ $regalos['tienda_texto'] ?? 'Tienda de Regalos' }}</p>
                        <p class="mt-1 text-sm opacity-60">Elige el regalo perfecto</p>
                    </a>
                @endif
            </div>
            @endif

            <!-- Opciones adicionales -->
            @if($hasOpciones)
                <div>
                    <p class="text-[11px] uppercase tracking-[0.2em] text-primary/60 font-semibold mb-3">Otras formas de contribuir</p>
                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach($opciones as $gift)
                            <article class="inv-card p-5 rounded-xl transition-all duration-300 hover:shadow-md hover:scale-[1.01] group">
                                @if(!empty($gift['icono']))
                                    @include('invitations.partials.icon', ['name' => $gift['icono'], 'class' => 'w-10 h-10 text-primary mb-3'])
                                @endif
                                <h3 class="font-title text-base text-primary font-semibold">{{ $gift['titulo'] ?? 'Opción' }}</h3>
                                @if(!empty($gift['descripcion']))
                                    <p class="mt-2 text-xs opacity-65 leading-relaxed">{{ $gift['descripcion'] }}</p>
                                @endif
                                @if(!empty($gift['enlace']))
                                    <a href="{{ $gift['enlace'] }}" target="_blank" rel="noopener" class="mt-4 inline-flex items-center gap-2 text-xs font-semibold text-primary hover:opacity-70 transition">
                                        Acceder
                                        @include('invitations.partials.icon', ['name' => 'arrow-right', 'class' => 'w-3 h-3', 'animated' => false])
                                    </a>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Lluvia de Sobres -->
            @if($hasSobres)
            <div class="inv-card rounded-2xl p-6 text-center bg-gradient-to-br from-primary/5 to-primary/0 border border-primary/10">
                <svg class="w-8 h-8 text-primary mx-auto mb-3 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <p class="font-title text-lg text-primary font-semibold">{{ $sobres['titulo'] ?? 'Lluvia de Sobres' }}</p>
                @if(!empty($sobres['direccion']))
                    <p class="text-sm opacity-70 mt-3 leading-relaxed">{{ $sobres['direccion'] }}</p>
                @endif
            </div>
            @endif
        </div>
    </div>

    <!-- Modal de Banco -->
    @if($hasBanco)
    <template x-teleport="body">
        <div x-show="showBank" x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="invitation-modal-backdrop"
            @click.self="showBank=false"
            @keydown.escape.window="showBank=false">
            <div class="invitation-modal-panel" @click.stop>
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        <h3 class="font-title text-xl text-primary font-semibold">Transferencia Bancaria</h3>
                    </div>
                    <button type="button" @click="showBank=false" class="w-8 h-8 rounded-full border border-stone-200 flex items-center justify-center text-stone-400 hover:text-stone-700 transition">
                        @include('invitations.partials.icon', ['name' => 'close', 'class' => 'w-4 h-4', 'animated' => false])
                    </button>
                </div>
                <div class="space-y-2 text-sm mb-6">
                    @foreach(['banco' => 'Banco', 'titular' => 'Titular', 'ci' => 'Cédula', 'cuenta' => 'Cuenta'] as $key => $label)
                        @if(!empty($banco[$key]))
                            <div class="flex justify-between items-center gap-4 p-3 rounded-lg bg-stone-50 dark:bg-stone-800">
                                <span class="text-stone-600 dark:text-stone-400 text-sm font-medium">{{ $label }}</span>
                                <span class="font-mono font-semibold text-right text-primary break-all">{{ $banco[$key] }}</span>
                            </div>
                        @endif
                    @endforeach
                </div>
                @if(!empty($banco['qr_url']))
                    <div class="mb-6 p-4 rounded-2xl bg-white border-2 border-primary/20 flex justify-center">
                        <img src="{{ $banco['qr_url'] }}" alt="QR Transferencia" class="w-52 h-52 object-contain">
                    </div>
                    <p class="text-[11px] text-center text-stone-500 uppercase tracking-widest mb-4">Escanea para transferir</p>
                @endif
                <button type="button" @click="showBank=false"
                    class="w-full py-3 rounded-xl bg-primary text-white text-sm font-semibold active:scale-[0.98] transition-transform hover:shadow-lg">
                    Listo
                </button>
            </div>
        </div>
    </template>
    @endif
</section>
@endif
