<section class="invitation-section py-16 px-6 z-10 relative" x-data="{ tab: 'regalos', showBank: false }">
    <div class="max-w-md mx-auto">
        <h2 class="font-title text-2xl text-center text-primary mb-6">{{ $regalos['titulo'] ?? 'Regalos' }}</h2>

        <div class="flex justify-center gap-4 mb-8 text-sm">
            <button @click="tab='regalos'" :class="tab==='regalos' ? 'text-primary border-b-2 border-primary' : 'opacity-50'">Regalos</button>
            <button @click="tab='sobres'" :class="tab==='sobres' ? 'text-primary border-b-2 border-primary' : 'opacity-50'">Sobres</button>
            <button @click="showBank=true" class="opacity-70 hover:text-primary">Transferencia</button>
        </div>

        <div x-show="tab==='regalos'" class="text-center">
            @if(!empty($regalos['tienda_url']))
                <a href="{{ $regalos['tienda_url'] }}" target="_blank" class="inline-block px-6 py-3 rounded-full bg-primary text-white text-sm">
                    {{ $regalos['tienda_texto'] ?? 'Ver lista de regalos' }}
                </a>
            @endif
        </div>

        <div x-show="tab==='sobres'" x-cloak class="text-center">
            <p class="font-title text-lg">{{ $regalos['sobres']['titulo'] ?? 'Lluvia de Sobres' }}</p>
            <p class="text-sm opacity-70 mt-2">{{ $regalos['sobres']['direccion'] ?? '' }}</p>
        </div>
    </div>

    {{-- Modal bancario --}}
    <div x-show="showBank" x-cloak class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/50 p-4" @click.self="showBank=false">
        <div class="bg-white rounded-3xl p-6 max-w-sm w-full text-center shadow-2xl">
            <h3 class="font-title text-xl text-primary mb-4">Datos Bancarios</h3>
            @php $banco = $regalos['banco'] ?? []; @endphp
            <div class="text-sm space-y-2 text-left">
                <p><span class="opacity-50">Banco:</span> {{ $banco['banco'] ?? '' }}</p>
                <p><span class="opacity-50">Titular:</span> {{ $banco['titular'] ?? '' }}</p>
                <p><span class="opacity-50">CI:</span> {{ $banco['ci'] ?? '' }}</p>
                <p><span class="opacity-50">Cuenta:</span> {{ $banco['cuenta'] ?? '' }}</p>
            </div>
            @if(!empty($banco['qr_url']))
                <img src="{{ $banco['qr_url'] }}" alt="QR Transferencia" class="mx-auto mt-4 w-40 h-40 object-contain">
            @endif
            <button @click="showBank=false" class="mt-6 text-sm text-primary underline">Cerrar</button>
        </div>
    </div>
</section>
