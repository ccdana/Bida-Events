<section class="invitation-section relative z-10" x-data="{ tab: 'sugerencias' }">
    <div class="section-inner-wide">
        <header class="text-center mb-10">
            @include('invitations.partials.icon', ['name' => 'shirt', 'class' => 'w-8 h-8 text-primary mx-auto mb-4'])
            <h2 class="font-title text-3xl text-primary">{{ $dressCode['titulo'] ?? 'Dress Code' }}</h2>
            <p class="font-title text-lg mt-3 opacity-80">{{ $dressCode['estilo'] ?? '' }}</p>
            <p class="text-sm opacity-60 mt-3 leading-relaxed max-w-xs mx-auto">{{ $dressCode['descripcion'] ?? '' }}</p>
        </header>

        {{-- Tabs --}}
        <div class="flex justify-center gap-1 mb-10 p-1 rounded-full bg-primary/5 border border-primary/10">
            @foreach(['sugerencias' => 'Sugerencias', 'colores' => 'Colores', 'evitar' => 'Evitar'] as $key => $label)
                <button type="button" @click="tab='{{ $key }}'"
                    class="px-4 py-2 rounded-full text-xs uppercase tracking-wider transition-all duration-300"
                    :class="tab === '{{ $key }}' ? 'bg-primary text-white shadow-sm' : 'opacity-50 hover:opacity-80'">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- Sugerencias de vestimenta --}}
        <div x-show="tab === 'sugerencias'" x-cloak class="space-y-4">
            @forelse($dressCode['sugerencias'] ?? [] as $i => $sug)
                <article class="rounded-2xl border border-primary/10 bg-white/60 overflow-hidden flex gap-0 transition hover:border-primary/30"
                    x-data="{ open: {{ $i === 0 ? 'true' : 'false' }} }">
                    @if(!empty($sug['imagen']))
                        <div class="w-24 shrink-0 bg-cover bg-center" style="background-image:url('{{ $sug['imagen'] }}')"></div>
                    @else
                        <div class="w-24 shrink-0 bg-gradient-to-br from-primary/20 to-primary/5 flex items-center justify-center">
                            @include('invitations.partials.icon', ['name' => 'shirt', 'class' => 'w-8 h-8 text-primary/50'])
                        </div>
                    @endif
                    <div class="flex-1 p-4">
                        <button type="button" @click="open = !open" class="w-full text-left flex items-center justify-between gap-2">
                            <div>
                                <p class="text-[10px] uppercase tracking-wider text-primary/70">{{ $sug['para'] ?? 'Invitados' }}</p>
                                <h3 class="font-title text-base mt-0.5">{{ $sug['titulo'] }}</h3>
                            </div>
                            <span class="shrink-0 w-6 h-6 rounded-full border border-primary/20 flex items-center justify-center transition-transform duration-300"
                                :class="open ? 'rotate-180 bg-primary/10' : ''">
                                @include('invitations.partials.icon', ['name' => 'chevron-down', 'class' => 'w-3 h-3', 'animated' => false])
                            </span>
                        </button>
                        <div x-show="open" x-cloak class="mt-3 pt-3 border-t border-primary/10">
                            <p class="text-sm opacity-70 leading-relaxed">{{ $sug['descripcion'] }}</p>
                            @if(!empty($sug['ejemplos']))
                                <ul class="mt-3 flex flex-wrap gap-2">
                                    @foreach($sug['ejemplos'] as $ej)
                                        <li class="text-[11px] px-3 py-1 rounded-full border border-primary/20 bg-primary/5">{{ $ej }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <p class="text-center text-sm opacity-50 py-8">Consulta con los anfitriones sobre la vestimenta ideal.</p>
            @endforelse
        </div>

        {{-- Colores permitidos --}}
        <div x-show="tab === 'colores'" x-cloak>
            <div class="grid grid-cols-2 gap-4">
                @foreach($dressCode['colores_permitidos'] ?? [] as $color)
                    <div class="rounded-2xl border border-primary/10 p-4 text-center bg-white/50 hover:scale-[1.02] transition-transform">
                        <div class="w-16 h-16 rounded-full mx-auto shadow-inner border-2 border-white ring-2 ring-primary/10" style="background: {{ $color['hex'] }}"></div>
                        <p class="text-sm font-medium mt-3">{{ $color['nombre'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Colores a evitar --}}
        <div x-show="tab === 'evitar'" x-cloak>
            <div class="space-y-3">
                @foreach($dressCode['colores_prohibidos'] ?? [] as $color)
                    <div class="flex items-center gap-4 rounded-2xl border border-red-200/60 bg-white/50 p-4">
                        <div class="relative w-12 h-12 rounded-full shrink-0 border-2 border-red-200" style="background: {{ $color['hex'] }}">
                            <span class="absolute inset-0 flex items-center justify-center">
                                @include('invitations.partials.icon', ['name' => 'close', 'class' => 'w-5 h-5 text-red-600', 'animated' => false])
                            </span>
                        </div>
                        <div>
                            <p class="font-medium text-sm">{{ $color['nombre'] }}</p>
                            @if(!empty($color['motivo']))
                                <p class="text-xs opacity-50 mt-0.5">{{ $color['motivo'] }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
