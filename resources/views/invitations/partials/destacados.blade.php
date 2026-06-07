<section class="invitation-section reveal" x-data="{ section: 'cortejo' }">
    <div class="section-inner-wide">
        <header class="section-header">
            <span class="section-eyebrow">Quién me acompaña</span>
            <h2 class="section-title">Mi cortejo</h2>
            <div class="section-ornament"></div>
        </header>

        <div class="flex justify-center gap-2 mb-8">
            @foreach(['cortejo' => 'Cortejo', 'padrinos' => 'Padrinos'] as $key => $label)
                <button type="button" @click="section='{{ $key }}'"
                    class="px-5 py-2.5 rounded-full text-xs uppercase tracking-widest border transition-all duration-300"
                    :class="section === '{{ $key }}' ? 'bg-primary text-white border-primary' : 'border-primary/20 opacity-60 inv-card-soft'">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div x-show="section === 'cortejo'" x-cloak>
            @if(!empty($destacados['chambelanes']))
            <div class="mb-10">
                <div class="flex items-center justify-center gap-2 mb-4">
                    @include('invitations.partials.icon', ['name' => 'users', 'class' => 'w-5 h-5 text-primary'])
                    <h3 class="font-title text-lg text-primary">Chambelanes</h3>
                </div>
                <div class="scroll-carousel" x-data="{ active: 0 }">
                    @foreach($destacados['chambelanes'] as $i => $persona)
                        @php
                            $nombre = is_array($persona) ? ($persona['nombre'] ?? '') : $persona;
                            $iniciales = is_array($persona) ? ($persona['iniciales'] ?? strtoupper(substr($nombre, 0, 2))) : strtoupper(substr($nombre, 0, 2));
                            $detalle = is_array($persona) ? ($persona['detalle'] ?? null) : null;
                        @endphp
                        <button type="button" @click="active = {{ $i }}"
                            class="scroll-carousel-item w-[9.5rem] rounded-2xl p-5 text-center transition-all duration-300 inv-card"
                            :class="active === {{ $i }} ? 'ring-2 ring-primary/40 shadow-md' : 'opacity-75'">
                            <div class="w-14 h-14 rounded-full mx-auto font-title text-lg flex items-center justify-center mb-3 transition-all duration-300"
                                :class="active === {{ $i }} ? 'bg-primary text-white' : 'inv-card-soft text-primary'">
                                {{ $iniciales }}
                            </div>
                            <p class="text-sm font-medium leading-tight">{{ $nombre }}</p>
                            @if($detalle)
                                <p class="text-[10px] opacity-50 mt-1.5 leading-snug" x-show="active === {{ $i }}" x-cloak>{{ $detalle }}</p>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>
            @endif

            @if(!empty($destacados['damitas']))
            <div>
                <div class="flex items-center justify-center gap-2 mb-4">
                    @include('invitations.partials.icon', ['name' => 'sparkle', 'class' => 'w-5 h-5 text-primary'])
                    <h3 class="font-title text-lg text-primary">Damitas</h3>
                </div>
                <div class="scroll-carousel" x-data="{ active: 0 }">
                    @foreach($destacados['damitas'] as $i => $persona)
                        @php
                            $nombre = is_array($persona) ? ($persona['nombre'] ?? '') : $persona;
                            $iniciales = is_array($persona) ? ($persona['iniciales'] ?? strtoupper(substr($nombre, 0, 2))) : strtoupper(substr($nombre, 0, 2));
                        @endphp
                        <button type="button" @click="active = {{ $i }}"
                            class="scroll-carousel-item w-[9.5rem] rounded-2xl p-5 text-center transition-all duration-300 inv-card"
                            :class="active === {{ $i }} ? 'ring-2 ring-primary/40 shadow-md' : 'opacity-75'">
                            <div class="w-14 h-14 rounded-full mx-auto font-title text-lg flex items-center justify-center mb-3 transition-all duration-300"
                                :class="active === {{ $i }} ? 'bg-primary text-white' : 'inv-card-soft text-primary'">
                                {{ $iniciales }}
                            </div>
                            <p class="text-sm font-medium">{{ $nombre }}</p>
                        </button>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <div x-show="section === 'padrinos'" x-cloak x-data="{ open: 0 }">
            <div class="flex items-center justify-center gap-2 mb-6">
                @include('invitations.partials.icon', ['name' => 'crown', 'class' => 'w-6 h-6 text-primary'])
                <h3 class="font-title text-lg text-primary">Nuestros Padrinos</h3>
            </div>
            <div class="space-y-3">
                @foreach($destacados['padrinos'] ?? [] as $i => $padrino)
                    <article class="inv-card overflow-hidden transition-all duration-300"
                        :class="open === {{ $i }} ? 'ring-1 ring-primary/30 shadow-md' : ''">
                        <button type="button" @click="open = open === {{ $i }} ? -1 : {{ $i }}"
                            class="w-full flex items-center justify-between gap-4 p-5 text-left">
                            <div>
                                <p class="text-[10px] uppercase tracking-[0.25em] text-primary/70">{{ $padrino['rol'] }}</p>
                                <p class="font-title text-lg mt-1" :class="open === {{ $i }} ? 'text-primary' : ''">{{ $padrino['nombres'] }}</p>
                            </div>
                            <span class="shrink-0 w-8 h-8 rounded-full border border-primary/20 flex items-center justify-center transition-transform duration-300 inv-card-soft"
                                :class="open === {{ $i }} ? 'rotate-180 bg-primary/10' : ''">
                                @include('invitations.partials.icon', ['name' => 'chevron-down', 'class' => 'w-4 h-4', 'animated' => false])
                            </span>
                        </button>
                        <div x-show="open === {{ $i }}" x-cloak class="px-5 pb-5 pt-0">
                            <div class="h-px bg-primary/10 mb-4"></div>
                            <p class="text-sm opacity-60 leading-relaxed">
                                {{ $padrino['mensaje'] ?? 'Gracias por acompañarnos y bendecir este momento tan especial.' }}
                            </p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>
