<section class="invitation-section reveal" id="dress-code" x-data="{ tab: 'sugerencias' }">
    <div class="section-inner-wide">
        <header class="section-header">
            @include('invitations.partials.icon', ['name' => 'shirt', 'class' => 'w-8 h-8 text-primary mx-auto mb-3'])
            <span class="section-eyebrow">Vestimenta</span>
            <h2 class="section-title">{{ $dressCode['titulo'] ?? 'Dress Code' }}</h2>
            <div class="section-ornament"></div>
            <div class="mt-4 mx-auto max-w-lg rounded-[1.5rem] p-4 text-left inv-card">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        @if(!empty($dressCode['estilo']))
                            <p class="font-title text-base text-secondary">{{ $dressCode['estilo'] }}</p>
                        @endif
                        @if(!empty($dressCode['descripcion']))
                            <p class="mt-1 text-sm opacity-65 leading-relaxed">{{ $dressCode['descripcion'] }}</p>
                        @endif
                    </div>
                    <div class="flex -space-x-2">
                        <span class="w-8 h-8 rounded-full border-2 border-white" style="background: var(--primary-color)"></span>
                        <span class="w-8 h-8 rounded-full border-2 border-white" style="background: var(--secondary-color)"></span>
                        <span class="w-8 h-8 rounded-full border-2 border-white" style="background: var(--accent-color)"></span>
                    </div>
                </div>
            </div>
        </header>

        <div class="flex justify-center gap-1 mb-8 p-1 rounded-full inv-card-soft max-w-md mx-auto">
            @foreach(['sugerencias' => 'Sugerencias', 'colores' => 'Colores', 'evitar' => 'Evitar'] as $key => $label)
                <button type="button" @click="tab='{{ $key }}'"
                    class="flex-1 px-3 py-2 rounded-full text-xs uppercase tracking-wider transition-all duration-300"
                    :class="tab === '{{ $key }}' ? 'bg-primary text-white shadow-sm' : 'opacity-50 hover:opacity-80'">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div x-show="tab === 'sugerencias'" x-cloak class="space-y-4">
            @forelse($dressCode['sugerencias'] ?? [] as $i => $sug)
                <article class="inv-card overflow-hidden transition hover:border-primary/30"
                    x-data="{ open: {{ $i === 0 ? 'true' : 'false' }} }">
                    <div class="flex">
                        @if(!empty($sug['imagen']))
                            <div class="w-28 shrink-0 bg-cover bg-center min-h-[9rem]" style="background-image:url('{{ $sug['imagen'] }}')"></div>
                        @else
                            <div class="w-28 shrink-0 flex items-center justify-center min-h-[9rem]" style="background: color-mix(in srgb, var(--primary-color) 10%, var(--accent-color))">
                                @include('invitations.partials.icon', ['name' => 'shirt', 'class' => 'w-9 h-9 text-primary/60'])
                            </div>
                        @endif
                        <div class="flex-1 p-4">
                            <button type="button" @click="open = !open" class="w-full text-left flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-[10px] uppercase tracking-wider text-primary/70">{{ $sug['para'] ?? 'Invitados' }}</p>
                                    <h3 class="font-title text-lg mt-0.5">{{ $sug['titulo'] }}</h3>
                                </div>
                                <span class="shrink-0 w-7 h-7 rounded-full border border-primary/20 flex items-center justify-center transition-transform duration-300"
                                    :class="open ? 'rotate-180 bg-primary/10' : ''">
                                    @include('invitations.partials.icon', ['name' => 'chevron-down', 'class' => 'w-3 h-3', 'animated' => false])
                                </span>
                            </button>
                            <div x-show="open" x-cloak class="mt-3 pt-3 border-t border-primary/10">
                                <p class="text-sm opacity-70 leading-relaxed">{{ $sug['descripcion'] }}</p>
                                @if(!empty($sug['ejemplos']))
                                    <ul class="mt-3 flex flex-wrap gap-2">
                                        @foreach($sug['ejemplos'] as $ej)
                                            <li class="text-[11px] px-3 py-1 rounded-full border border-primary/20 inv-card-soft">{{ $ej }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <p class="text-center text-sm opacity-50 py-8">Consulta con los anfitriones sobre la vestimenta ideal.</p>
            @endforelse
        </div>

        <div x-show="tab === 'colores'" x-cloak>
            <div class="grid grid-cols-2 gap-4">
                @foreach($dressCode['colores_permitidos'] ?? [] as $color)
                    <div class="inv-card p-4 text-center hover:scale-[1.02] transition-transform">
                        <div class="w-16 h-16 rounded-full mx-auto shadow-inner border-2 border-white ring-2 ring-primary/15" style="background: {{ $color['hex'] }}"></div>
                        <p class="text-sm font-medium mt-3">{{ $color['nombre'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div x-show="tab === 'evitar'" x-cloak>
            <div class="space-y-3">
                @forelse($dressCode['colores_prohibidos'] ?? [] as $color)
                    <div class="inv-card flex items-center gap-4 p-4">
                        <div class="relative w-14 h-14 rounded-full shrink-0 border-2 border-primary/20 overflow-hidden" style="background: {{ $color['hex'] }}">
                            <span class="absolute inset-0 flex items-center justify-center">
                                <span class="block w-full h-0.5 bg-primary rotate-45 absolute"></span>
                            </span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-title text-sm">{{ $color['nombre'] }}</p>
                            @if(!empty($color['motivo']))
                                <p class="text-xs opacity-50 mt-0.5">{{ $color['motivo'] }}</p>
                            @endif
                        </div>
                        <span class="shrink-0 text-[10px] uppercase tracking-wider text-primary/50 px-2 py-1 rounded-full inv-card-soft">Evitar</span>
                    </div>
                @empty
                    <p class="text-center text-sm opacity-50 py-6">No hay colores restringidos</p>
                @endforelse
            </div>
        </div>
    </div>
</section>
