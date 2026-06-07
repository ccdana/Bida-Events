<section class="invitation-section reveal" x-data="{ tab: 'regalos', showBank: false }"
    x-effect="document.body.style.overflow = showBank ? 'hidden' : ''">
    <div class="section-inner-wide">
        <header class="section-header">
            <span class="section-eyebrow">Detalles</span>
            <h2 class="section-title">{{ $regalos['titulo'] ?? 'Regalos' }}</h2>
            <div class="section-ornament"></div>
        </header>

        <div class="flex justify-center gap-1 mb-6 p-1 rounded-full bg-primary/5 border border-primary/10 max-w-xs mx-auto">
            <button type="button" @click="tab='regalos'"
                class="flex-1 px-4 py-2 rounded-full text-xs uppercase tracking-wider transition-all duration-300"
                :class="tab==='regalos' ? 'bg-primary text-white shadow-sm' : 'opacity-50'">Regalos</button>
            <button type="button" @click="tab='sobres'"
                class="flex-1 px-4 py-2 rounded-full text-xs uppercase tracking-wider transition-all duration-300"
                :class="tab==='sobres' ? 'bg-primary text-white shadow-sm' : 'opacity-50'">Sobres</button>
            <button type="button" @click="showBank=true"
                class="flex-1 px-4 py-2 rounded-full text-xs uppercase tracking-wider transition-all duration-300 opacity-60 hover:opacity-100">
                Banco
            </button>
        </div>

        <div x-show="tab==='regalos'" class="text-center">
            @if(!empty($regalos['tienda_url']))
                <a href="{{ $regalos['tienda_url'] }}" target="_blank" rel="noopener"
                    class="inline-flex items-center gap-2 px-8 py-3.5 rounded-full bg-primary text-white text-sm font-medium shadow-lg active:scale-[0.98] transition-transform">
                    @include('invitations.partials.icon', ['name' => 'gift', 'class' => 'w-4 h-4', 'animated' => false])
                    {{ $regalos['tienda_texto'] ?? 'Ver lista de regalos' }}
                </a>
            @else
                <p class="text-sm opacity-50 py-4">Tu presencia es el mejor regalo</p>
            @endif
        </div>

        <div x-show="tab==='sobres'" x-cloak class="text-center glass-card p-6">
            <p class="font-title text-lg text-primary">{{ $regalos['sobres']['titulo'] ?? 'Lluvia de Sobres' }}</p>
            @if(!empty($regalos['sobres']['direccion']))
                <p class="text-sm opacity-70 mt-3 leading-relaxed">{{ $regalos['sobres']['direccion'] }}</p>
            @endif
        </div>
    </div>

    {{-- Modal bancario (teleport al body para evitar conflictos de z-index) --}}
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
