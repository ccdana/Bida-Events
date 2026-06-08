<section class="invitation-section reveal" x-data="{ showBank: false }"
    x-effect="document.body.style.overflow = showBank ? 'hidden' : ''">
    <div class="section-inner-wide">
        <header class="section-header">
            <span class="section-eyebrow">Detalles</span>
            <h2 class="section-title">{{ $regalos['titulo'] ?? 'Regalos' }}</h2>
            <div class="section-ornament"></div>
        </header>

        <div class="space-y-4">
            <div class="grid gap-3 sm:grid-cols-3">
                <button type="button" @click="showBank=true" class="inv-card rounded-[1.25rem] p-4 text-left sm:col-span-1">
                    <p class="text-[10px] uppercase tracking-[0.2em] text-primary/60">Banco</p>
                    <p class="mt-2 font-title text-lg">Ver datos</p>
                    <p class="mt-1 text-sm opacity-60">Transferencia y QR.</p>
                </button>
                <div class="inv-card rounded-[1.25rem] p-4 sm:col-span-2">
                    @if(!empty($regalos['tienda_url']))
                        <a href="{{ $regalos['tienda_url'] }}" target="_blank" rel="noopener"
                            class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-primary text-white text-sm font-medium shadow-lg active:scale-[0.98] transition-transform">
                            @include('invitations.partials.icon', ['name' => 'gift', 'class' => 'w-4 h-4', 'animated' => false])
                            {{ $regalos['tienda_texto'] ?? 'Ver lista de regalos' }}
                        </a>
                    @else
                        <p class="text-sm opacity-50 py-4">Tu presencia es el mejor regalo</p>
                    @endif
                </div>
            </div>

            @if(!empty($regalos['opciones']))
                <div class="grid gap-3 md:grid-cols-2">
                    @foreach($regalos['opciones'] as $gift)
                        <article class="inv-card p-4">
                            <p class="text-[10px] uppercase tracking-[0.2em] text-primary/60">{{ $gift['icono'] ?? 'gift' }}</p>
                            <h3 class="font-title text-lg mt-2">{{ $gift['titulo'] ?? 'Opción' }}</h3>
                            @if(!empty($gift['descripcion']))
                                <p class="mt-1 text-sm opacity-65 leading-relaxed">{{ $gift['descripcion'] }}</p>
                            @endif
                            @if(!empty($gift['enlace']))
                                <a href="{{ $gift['enlace'] }}" target="_blank" rel="noopener" class="mt-4 inline-flex items-center gap-2 text-sm font-medium text-primary">
                                    Abrir enlace
                                    @include('invitations.partials.icon', ['name' => 'arrow-right', 'class' => 'w-4 h-4', 'animated' => false])
                                </a>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif

            <div class="inv-card rounded-[1.25rem] p-5 text-center">
                <p class="font-title text-lg text-primary">{{ $regalos['sobres']['titulo'] ?? 'Lluvia de Sobres' }}</p>
                @if(!empty($regalos['sobres']['direccion']))
                    <p class="text-sm opacity-70 mt-3 leading-relaxed">{{ $regalos['sobres']['direccion'] }}</p>
                @endif
            </div>
        </div>
    </div>

    <template x-teleport="body">
        <div x-show="showBank" x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="invitation-modal-backdrop"
            @click.self="showBank=false"
            @keydown.escape.window="showBank=false">
            <div class="invitation-modal-panel" @click.stop>
                <div class="flex items-center justify-between mb-5">
                    <h3 class="font-title text-xl text-primary">Transferencia</h3>
                    <button type="button" @click="showBank=false" class="w-8 h-8 rounded-full border border-stone-200 flex items-center justify-center text-stone-400 hover:text-stone-700">
                        @include('invitations.partials.icon', ['name' => 'close', 'class' => 'w-4 h-4', 'animated' => false])
                    </button>
                </div>
                @php $banco = $regalos['banco'] ?? []; @endphp
                <div class="space-y-3 text-sm">
                    @foreach(['banco' => 'Banco', 'titular' => 'Titular', 'ci' => 'CI', 'cuenta' => 'Cuenta'] as $key => $label)
                        @if(!empty($banco[$key]))
                            <div class="flex justify-between gap-3 py-2 border-b border-stone-100">
                                <span class="text-stone-400 shrink-0">{{ $label }}</span>
                                <span class="font-medium text-right break-all">{{ $banco[$key] }}</span>
                            </div>
                        @endif
                    @endforeach
                </div>
                @if(!empty($banco['qr_url']))
                    <div class="mt-5 p-4 rounded-2xl bg-stone-50 border border-stone-100">
                        <img src="{{ $banco['qr_url'] }}" alt="QR Transferencia" class="mx-auto w-44 h-44 object-contain">
                        <p class="text-[10px] text-center text-stone-400 mt-2 uppercase tracking-wider">Escanea para transferir</p>
                    </div>
                @endif
                <button type="button" @click="showBank=false"
                    class="mt-6 w-full py-3 rounded-xl bg-primary text-white text-sm font-medium active:scale-[0.98] transition-transform">
                    Cerrar
                </button>
            </div>
        </div>
    </template>
</section>
