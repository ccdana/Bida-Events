<div x-show="activeTab === 'regalos'" x-cloak class="space-y-4"
    x-data="{
        sobresActive: !!(modules.regalos?.sobres?.titulo || modules.regalos?.sobres?.direccion),
        bancoActive: !!(modules.regalos?.banco?.banco || modules.regalos?.banco?.titular || modules.regalos?.banco?.cuenta),
        opcionesActive: (modules.regalos?.opciones?.length > 0),
    }">

    {{-- Encabezado --}}
    <section class="admin-card space-y-4">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="admin-eyebrow">Regalos</p>
                <h2 class="font-serif text-xl text-stone-950">Mesa de regalos</h2>
                <p class="mt-1 text-sm text-stone-500">Activa solo las opciones que quieras ofrecer a tus invitados.</p>
            </div>
            <label class="admin-toggle-row shrink-0">
                <input type="checkbox" x-model="modules.config.modulos.regalos" @change="schedulePreview()" class="rounded border-stone-300 text-amber-600 focus:ring-amber-500">
                <span class="text-stone-700">Activo</span>
            </label>
        </div>

        {{-- Título sección --}}
        <div>
            <label class="admin-label">Título de la sección</label>
            <input type="text" x-model="modules.regalos.titulo" @input="schedulePreview()"
                class="admin-input" placeholder="Ej. Mesa de regalos, Con cariño…">
        </div>

        {{-- Tienda externa (siempre visible, opcional) --}}
        <div class="rounded-2xl border border-stone-200 bg-stone-50/60 p-4 space-y-3">
            <div class="flex items-center gap-2 mb-1">
                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-white border border-stone-200 text-stone-500">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </span>
                <p class="text-sm font-semibold text-stone-800">Tienda de regalos externa</p>
                <span class="ml-auto text-[10px] text-stone-400 uppercase tracking-wider">Opcional</span>
            </div>
            <div>
                <label class="admin-label">URL de la tienda</label>
                <input type="url" x-model="modules.regalos.tienda_url" @input="schedulePreview()"
                    class="admin-input" placeholder="https://...">
            </div>
            <div>
                <label class="admin-label">Texto del botón</label>
                <input type="text" x-model="modules.regalos.tienda_texto" @input="schedulePreview()"
                    class="admin-input" placeholder="Ej. Ver lista de regalos">
            </div>
        </div>
    </section>

    {{-- Lluvia de sobres --}}
    <section class="admin-card space-y-0 overflow-hidden">
        <button type="button" @click="sobresActive = !sobresActive"
            class="w-full flex items-center gap-3 text-left group">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl"
                :class="sobresActive ? 'bg-amber-100 text-amber-700' : 'bg-stone-100 text-stone-400'">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </span>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-stone-900">Lluvia de sobres</p>
                <p class="text-xs text-stone-400">Dirección física para recibir sobres de regalo</p>
            </div>
            <span class="shrink-0 text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full transition"
                :class="sobresActive ? 'bg-amber-100 text-amber-700' : 'bg-stone-100 text-stone-500'"
                x-text="sobresActive ? 'Activo' : 'Inactivo'">
            </span>
            <svg class="w-4 h-4 text-stone-400 shrink-0 transition-transform duration-200"
                :class="sobresActive ? 'rotate-180' : ''"
                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="sobresActive" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="mt-4 space-y-3 pt-4 border-t border-stone-100">
            <div>
                <label class="admin-label">Título de la sección sobres</label>
                <input type="text" x-model="modules.regalos.sobres.titulo" @input="schedulePreview()"
                    class="admin-input" placeholder="Ej. Lluvia de sobres, Tu sobre es el mejor regalo">
            </div>
            <div>
                <label class="admin-label">Dirección física</label>
                <input type="text" x-model="modules.regalos.sobres.direccion" @input="schedulePreview()"
                    class="admin-input" placeholder="Ej. Av. Siempre Viva 742, Ciudad">
            </div>
        </div>
    </section>

    {{-- Datos bancarios --}}
    <section class="admin-card space-y-0 overflow-hidden">
        <button type="button" @click="bancoActive = !bancoActive"
            class="w-full flex items-center gap-3 text-left group">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl"
                :class="bancoActive ? 'bg-emerald-100 text-emerald-700' : 'bg-stone-100 text-stone-400'">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v9a2 2 0 002 2z"/>
                </svg>
            </span>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-stone-900">Transferencia bancaria</p>
                <p class="text-xs text-stone-400">Datos de cuenta y QR para transferencias</p>
            </div>
            <span class="shrink-0 text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full transition"
                :class="bancoActive ? 'bg-emerald-100 text-emerald-700' : 'bg-stone-100 text-stone-500'"
                x-text="bancoActive ? 'Activo' : 'Inactivo'">
            </span>
            <svg class="w-4 h-4 text-stone-400 shrink-0 transition-transform duration-200"
                :class="bancoActive ? 'rotate-180' : ''"
                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="bancoActive" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="mt-4 space-y-3 pt-4 border-t border-stone-100">
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                <div>
                    <label class="admin-label">Banco</label>
                    <input type="text" x-model="modules.regalos.banco.banco" @input="schedulePreview()"
                        class="admin-input" placeholder="Ej. Banco Unión">
                </div>
                <div>
                    <label class="admin-label">Titular</label>
                    <input type="text" x-model="modules.regalos.banco.titular" @input="schedulePreview()"
                        class="admin-input" placeholder="Nombre completo">
                </div>
                <div>
                    <label class="admin-label">CI / NIT</label>
                    <input type="text" x-model="modules.regalos.banco.ci" @input="schedulePreview()"
                        class="admin-input" placeholder="1234567">
                </div>
                <div>
                    <label class="admin-label">N° de cuenta</label>
                    <input type="text" x-model="modules.regalos.banco.cuenta" @input="schedulePreview()"
                        class="admin-input" placeholder="0000-0000-0000">
                </div>
            </div>
            @include('admin.partials.cloudinary-upload', [
                'label' => 'QR de transferencia',
                'type' => 'image',
                'context' => 'qr-banco',
                'accept' => 'image/jpeg,image/png,image/webp',
                'previewExpr' => 'modules.regalos.banco.qr_url',
            ])
        </div>
    </section>

    {{-- Más opciones de regalo --}}
    <section class="admin-card space-y-0 overflow-hidden">
        <button type="button" @click="opcionesActive = !opcionesActive"
            class="w-full flex items-center gap-3 text-left group">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl"
                :class="opcionesActive ? 'bg-violet-100 text-violet-700' : 'bg-stone-100 text-stone-400'">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <rect x="3" y="8" width="18" height="13" rx="1"/><path d="M12 8v13M3 12h18M12 8c-2 0-3-1.5-3-3s1.5-2 3-2 3 1 3 2-1 3-3 3z" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-stone-900">Más opciones de regalo</p>
                <p class="text-xs text-stone-400">Tarjetas personalizadas con enlace, descripción e ícono</p>
            </div>
            <span x-show="modules.regalos.opciones.length > 0" x-cloak
                class="shrink-0 text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full bg-violet-100 text-violet-700"
                x-text="modules.regalos.opciones.length + (modules.regalos.opciones.length === 1 ? ' opción' : ' opciones')">
            </span>
            <svg class="w-4 h-4 text-stone-400 shrink-0 transition-transform duration-200"
                :class="opcionesActive ? 'rotate-180' : ''"
                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="opcionesActive" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="mt-4 pt-4 border-t border-stone-100 space-y-3">
            <template x-for="(option, i) in modules.regalos.opciones" :key="'gift-'+i">
                <div class="rounded-xl border border-stone-200 bg-stone-50 p-3.5 space-y-3">
                    <div class="flex items-center justify-between gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-stone-200 bg-white px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-stone-500">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <rect x="3" y="8" width="18" height="13" rx="1"/><path d="M12 8v13M3 12h18M12 8c-2 0-3-1.5-3-3s1.5-2 3-2 3 1 3 2-1 3-3 3z" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Opción <span x-text="i + 1"></span>
                        </span>
                        <button type="button" @click="removeGiftOption(i)"
                            class="flex items-center gap-1 rounded-lg px-2.5 py-1 text-[11px] font-medium text-red-500 hover:bg-red-50 hover:text-red-600 transition">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            Eliminar
                        </button>
                    </div>
                    <div class="grid gap-2 md:grid-cols-2">
                        <div>
                            <label class="admin-label">Título</label>
                            <input type="text" x-model="option.titulo" @input="schedulePreview()" class="admin-input" placeholder="Ej. Lista en Falabella">
                        </div>
                        <div>
                            <label class="admin-label">Ícono</label>
                            @include('admin.partials.icon-picker', [
                                'model'  => 'option.icono',
                                'icons'  => ['gift','star','crown','sparkle','music','camera','users','map-pin','calendar','shirt','poll','glass','candle','dance','dinner'],
                                'change' => 'schedulePreview()',
                            ])
                        </div>
                    </div>
                    <div>
                        <label class="admin-label">Descripción</label>
                        <textarea x-model="option.descripcion" @input="schedulePreview()" rows="2" class="admin-input" placeholder="Descripción breve (opcional)"></textarea>
                    </div>
                    <div>
                        <label class="admin-label">URL de referencia</label>
                        <input type="url" x-model="option.enlace" @input="schedulePreview()" class="admin-input" placeholder="https://...">
                    </div>
                </div>
            </template>

            <button type="button" @click="addGiftOption(); opcionesActive = true"
                class="w-full flex items-center justify-center gap-2 rounded-xl border-2 border-dashed border-stone-200 bg-stone-50 py-3 text-xs font-semibold text-stone-500 hover:border-stone-300 hover:bg-stone-100 hover:text-stone-700 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Agregar opción
            </button>
        </div>
    </section>
</div>
