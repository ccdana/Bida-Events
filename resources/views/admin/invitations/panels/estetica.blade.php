{{-- Colores y tipografías: cada opción explica dónde se usa y se resalta en la muestra al pasar el cursor --}}
<div x-show="activeTab === 'estetica'" x-cloak class="space-y-4"
    x-data="{
        focus: null,
        spot(key) { return this.focus === key ? 'outline-2 outline-dashed outline-offset-4 outline-site-accent' : ''; },
    }">
    @include('admin.partials.panel-intro', [
        'eyebrow' => 'Estética',
        'title' => 'Colores y tipografías',
        'description' => 'los colores y las letras de toda la invitación.',
        'tip' => 'Pasa el cursor por un color o una tipografía: en la muestra se marca dónde se usa.',
    ])

    {{-- Muestra que acompaña al elegir --}}
    <section class="admin-card sticky top-0 z-10 overflow-hidden p-0">
        <div class="px-5 pb-5 pt-6 text-center transition-colors"
            :class="focus === 'background' ? 'outline-2 outline-dashed -outline-offset-8 outline-site-accent' : ''"
            :style="`background:${modules.config.colores.background};color:${modules.config.colores.text};font-family:'${modules.config.tipografias.cuerpo}', sans-serif`">
            <p class="inline-block text-[10px] uppercase tracking-[0.3em]" :class="spot('text')"
                x-text="modules.bienvenida?.subtitulo || profile.sample?.subtitle"></p>
            <p class="mx-auto mt-1 block w-fit px-2 text-4xl leading-tight" :class="spot('script')"
                :style="`font-family:'${modules.config.tipografias.script}', cursive`"
                x-text="modules.bienvenida?.nombre_quinceanera || modules.dedicatoria?.para || profile.sample?.name"></p>
            <div class="mx-auto mt-3 h-px w-16" :class="spot('primary')" :style="`background:${modules.config.colores.primary}`"></div>

            <div class="mt-4 rounded-md px-4 py-3" :class="spot('accent')"
                :style="`background:color-mix(in srgb, ${modules.config.colores.accent} 35%, ${modules.config.colores.background})`">
                <p class="mx-auto w-fit px-1 text-lg" :class="spot('titulos')" :style="`font-family:'${modules.config.tipografias.titulos}', serif`">Itinerario</p>
                <p class="mx-auto mt-1 w-fit px-1 text-xs" :class="spot('cuerpo')">Recepción de invitados, 18:00</p>
            </div>

            <span class="mt-4 inline-block rounded-md px-3 py-1.5 text-xs font-semibold" :class="spot('primary')"
                :style="`background:${modules.config.colores.primary};color:${modules.config.colores.background}`">Confirmar asistencia</span>
        </div>
        {{-- Contraste medido sobre las mezclas reales de la invitación, no solo texto sobre fondo --}}
        <div class="border-t border-site-line px-4 py-3">
            <div class="flex items-center justify-between gap-3 text-xs">
                <span class="text-site-muted">Legibilidad de la paleta</span>
                <span class="admin-status-badge" :class="contrastIssues().length === 0 ? 'is-active' : 'is-declined'">
                    <span class="admin-status-dot"></span>
                    <span x-text="contrastIssues().length === 0
                        ? 'Todo se lee bien'
                        : (contrastIssues().length === 1 ? '1 tono difícil de leer' : contrastIssues().length + ' tonos difíciles de leer')"></span>
                </span>
            </div>

            <dl class="mt-2 grid grid-cols-2 gap-x-4 gap-y-1 text-[11px]">
                <template x-for="check in contrastChecks()" :key="check.label">
                    <div class="flex items-center justify-between gap-2 border-b border-site-line/60 py-0.5">
                        <dt class="truncate text-site-muted" x-text="check.label"></dt>
                        <dd class="shrink-0 font-medium tabular-nums"
                            :class="check.ratio >= check.min ? 'text-site-muted' : 'text-site-danger'"
                            x-text="check.ratio.toFixed(1) + ':1'"></dd>
                    </div>
                </template>
            </dl>

            <p class="mt-2 text-[11px] text-site-muted" x-show="contrastIssues().length > 0" x-cloak>
                Los tonos marcados no llegan al mínimo que se lee con comodidad (4.5:1, o 3:1 en texto grande).
                Prueba con un fondo más claro o un color de evento más oscuro.
            </p>
        </div>
    </section>

    {{-- Paletas completas: primero las de este tipo de evento (config/palettes.php) --}}
    <section class="admin-card space-y-4 p-4">
        <div>
            <h3 class="text-sm font-semibold">Paletas listas</h3>
            <p class="mt-0.5 text-xs text-site-muted">Aplica una combinación completa y después ajusta el color que quieras.</p>
        </div>

        @foreach([
            ['fn' => 'getEventColorPresets()', 'title' => 'Para esta plantilla', 'note' => 'La primera son los colores originales del diseño.'],
            ['fn' => 'getGeneralColorPresets()', 'title' => 'Otras paletas', 'note' => 'Sirven para cualquier evento.'],
        ] as $group)
            <div x-show="{{ $group['fn'] }}.length" x-cloak>
                <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-site-muted">{{ $group['title'] }}</p>
                <p class="mt-0.5 text-[11px] text-site-muted">{{ $group['note'] }}</p>
                <div class="mt-2 grid grid-cols-2 gap-2">
                    <template x-for="preset in {{ $group['fn'] }}" :key="preset.name">
                        <button type="button" @click="applyColorPreset(preset.name)"
                            class="rounded-[12px] border p-2.5 text-left transition-colors"
                            :class="isCurrentPreset(preset.name) ? 'border-site-ink bg-site-bg' : 'border-site-line hover:bg-site-bg'"
                            :aria-pressed="isCurrentPreset(preset.name).toString()">
                            <span class="flex h-9 items-center gap-1.5 overflow-hidden rounded-md border border-site-line px-1.5" :style="`background:${preset.colors.background}`">
                                <span class="h-5 flex-1 rounded-sm" :style="`background:${preset.colors.accent}`"></span>
                                <span class="size-3 shrink-0 rounded-full" :style="`background:${preset.colors.primary}`"></span>
                                <span class="h-1.5 w-6 shrink-0 rounded-full" :style="`background:${preset.colors.text}`"></span>
                            </span>
                            <span class="mt-2 flex items-center justify-between gap-1">
                                <span class="truncate text-xs font-semibold" x-text="preset.name"></span>
                                <x-phosphor-check-circle-fill class="size-4 shrink-0 text-site-accent" x-show="isCurrentPreset(preset.name)" aria-hidden="true" />
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span class="truncate text-[11px] text-site-muted" x-text="preset.description || ''"></span>
                                <span class="shrink-0 text-[10px] text-site-muted" x-show="preset.mode === 'night'" x-cloak>· de noche</span>
                            </span>
                        </button>
                    </template>
                </div>
            </div>
        @endforeach
    </section>

    {{-- Colores con su uso --}}
    <section class="admin-card space-y-3 p-4">
        <div>
            <h3 class="text-sm font-semibold">Colores</h3>
            <p class="mt-0.5 text-xs text-site-muted">Cada color tiene una función en la invitación.</p>
        </div>
        <div class="space-y-2">
            <template x-for="role in colorRoles" :key="role.key">
                <div class="flex items-center gap-3 rounded-[12px] border p-2.5 transition-colors"
                    :class="focus === role.key ? 'border-site-ink bg-site-bg' : 'border-site-line'"
                    @mouseenter="focus = role.key" @mouseleave="focus = null" @focusin="focus = role.key" @focusout="focus = null">
                    <label class="relative size-10 shrink-0 cursor-pointer overflow-hidden rounded-full border border-site-line"
                        :style="`background:${modules.config.colores[role.key]}`" :title="`Elegir color: ${role.label}`">
                        <input type="color" x-model="modules.config.colores[role.key]" class="absolute inset-0 size-full cursor-pointer opacity-0"
                            :aria-label="`Elegir color: ${role.label}`">
                    </label>
                    <div class="min-w-0 flex-1" :class="role.unused ? 'opacity-60' : ''">
                        <p class="text-sm font-semibold" x-text="role.label"></p>
                        <p class="text-xs leading-snug text-site-muted" x-text="role.usage"></p>
                    </div>
                    <input type="text" x-model="modules.config.colores[role.key]" @change="validateHexColor(role.key)" maxlength="7"
                        class="admin-hex-input shrink-0" :aria-label="`Código del color ${role.label}`">
                </div>
            </template>
        </div>
    </section>

    {{-- Tipografías con muestra de texto real --}}
    <section class="admin-card space-y-5 p-4">
        <div>
            <h3 class="text-sm font-semibold">Tipografías</h3>
            <p class="mt-0.5 text-xs text-site-muted">Cada opción se muestra con el texto donde se va a usar.</p>
        </div>
        <template x-for="role in fontRoles" :key="role.key">
            <div class="space-y-2" @mouseenter="focus = role.key" @mouseleave="focus = null" @focusin="focus = role.key" @focusout="focus = null">
                <div class="flex items-baseline justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold" x-text="role.label"></p>
                        <p class="text-xs leading-snug text-site-muted" x-text="role.usage"></p>
                    </div>
                    <span class="shrink-0 text-xs text-site-muted" x-text="modules.config.tipografias[role.key]"></span>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <template x-for="font in fontOptions[role.key]" :key="font">
                        <button type="button" @click="modules.config.tipografias[role.key] = font"
                            class="min-w-0 rounded-[10px] border px-3 py-2 text-left transition-colors"
                            :class="modules.config.tipografias[role.key] === font ? 'border-site-ink bg-site-bg' : 'border-site-line hover:bg-site-bg'"
                            :aria-pressed="(modules.config.tipografias[role.key] === font).toString()">
                            <span class="block truncate leading-snug" :class="role.size"
                                :style="`font-family:'${font}', ${role.fallback}`" x-text="fontSample(role.key)"></span>
                            <span class="mt-0.5 block truncate text-[11px] text-site-muted" x-text="font"></span>
                        </button>
                    </template>
                </div>
            </div>
        </template>
    </section>
</div>
