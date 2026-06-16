<div x-show="activeTab === 'general'" x-cloak class="space-y-2">
    <!-- Identidad de la invitación -->
    <section class="admin-card p-3 space-y-3">
        <div class="flex items-center justify-between gap-2">
            <div class="min-w-0">
                <p class="admin-eyebrow mb-0.5">Información General</p>
                <p class="text-xs text-stone-600 truncate">Datos básicos del evento</p>
            </div>
            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-white border border-stone-200 text-xs font-semibold shrink-0 text-blue-700 bg-blue-50 border-blue-200">
                <span class="inline-block w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                <span x-text="`${activeModulesCount} módulos`"></span>
            </span>
        </div>

        <div class="grid gap-2">
            <div>
                <label class="admin-label">Título del evento</label>
                <input type="text" x-model="meta.title" @input="onTitleInput()" class="admin-input" placeholder="Boda de Ana y Luis" required>
            </div>
            <div>
                <label class="admin-label">URL pública (slug)</label>
                <div class="flex items-center gap-1.5">
                    <span class="text-xs text-stone-400 shrink-0">/p/</span>
                    <input type="text" x-model="meta.slug" @input="slugManual = true" class="admin-input" required>
                </div>
            </div>
        </div>
    </section>

    <!-- Configuración del evento -->
    <section class="admin-card p-3 space-y-2">
        <p class="admin-eyebrow mb-1">Configuración</p>

        <div class="grid gap-2">
            <div x-data="{ open: false }" class="admin-accordion">
                <button type="button" @click="open = !open" class="admin-accordion-trigger">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-stone-900 text-left">Tipo de evento</p>
                        <p class="text-xs text-stone-500 text-left truncate" x-text="getEventTypeName()"></p>
                    </div>
                    <svg class="w-4 h-4 flex-shrink-0 transition-transform text-stone-500" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                    </svg>
                </button>
                <div x-show="open" class="admin-accordion-panel space-y-1">
                    <template x-for="type in eventTypes" :key="type.id">
                        <button type="button"
                            @click="meta.event_type_id = type.id; open = false"
                            class="admin-accordion-option"
                            :class="String(meta.event_type_id) === String(type.id) ? 'is-selected' : ''">
                            <span x-text="type.name"></span>
                        </button>
                    </template>
                </div>
            </div>

            <div x-data="{ open: false }" class="admin-accordion">
                <button type="button" @click="open = !open" class="admin-accordion-trigger">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-stone-900 text-left">Plantilla</p>
                        <p class="text-xs text-stone-500 text-left truncate" x-text="getTemplateLabel()"></p>
                    </div>
                    <svg class="w-4 h-4 flex-shrink-0 transition-transform text-stone-500" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                    </svg>
                </button>
                <div x-show="open" class="admin-accordion-panel space-y-1">
                    <template x-for="option in templateOptions" :key="option.value">
                        <button type="button"
                            @click="meta.template = option.value; open = false"
                            class="admin-accordion-option"
                            :class="meta.template === option.value ? 'is-selected' : ''">
                            <span x-text="option.label"></span>
                        </button>
                    </template>
                </div>
            </div>
        </div>
    </section>

    <!-- Fechas importantes -->
    <section class="admin-card p-3 space-y-2">
        <p class="admin-eyebrow mb-1">Fechas</p>

        <div class="grid gap-2">
            <div class="admin-date-field">
                <label class="admin-label">Día del evento</label>
                <input type="date" x-model="eventDatePart" class="admin-input admin-input-native-date" required>
            </div>
            <div class="admin-date-field">
                <label class="admin-label">Hora del evento</label>
                <input type="time" x-model="eventTimePart" class="admin-input admin-input-native-date" required>
            </div>
            <div class="admin-date-field">
                <label class="admin-label">La invitación expira el</label>
                <input type="date" x-model="meta.expires_at" class="admin-input admin-input-native-date" required>
            </div>
        </div>
    </section>

    <!-- Cliente asignado -->
    <section class="admin-card p-3 space-y-2">
        <p class="admin-eyebrow mb-1">Cliente Asignado</p>

        <div x-data="{ open: false }" class="admin-accordion">
            <button type="button" @click="open = !open" class="admin-accordion-trigger">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-stone-900 text-left" x-text="getAssignedClient()?.name ?? 'Sin cliente asignado'"></p>
                    <p class="text-xs text-stone-500 text-left truncate" x-text="getAssignedClient()?.email ?? 'Selecciona un cliente del listado'"></p>
                </div>
                <svg class="w-4 h-4 flex-shrink-0 transition-transform text-stone-500" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                </svg>
            </button>
            <div x-show="open" class="admin-accordion-panel space-y-1">
                <button type="button"
                    @click="meta.user_id = ''; onClientChange(); open = false"
                    class="admin-accordion-option"
                    :class="!meta.user_id ? 'is-selected' : ''">
                    Sin cliente asignado
                </button>
                <template x-for="client in clients" :key="client.id">
                    <button type="button"
                        @click="meta.user_id = client.id; onClientChange(); open = false"
                        class="admin-accordion-option"
                        :class="String(meta.user_id) === String(client.id) ? 'is-selected' : ''">
                        <span class="block font-medium" x-text="client.name"></span>
                        <span class="block text-xs text-stone-500 font-normal" x-text="client.email"></span>
                    </button>
                </template>
            </div>
        </div>

        <template x-if="meta.user_id && getAssignedClient()">
            <div class="mt-2 p-2.5 rounded-lg border border-emerald-200 bg-emerald-50 space-y-1">
                <p class="text-sm font-semibold text-emerald-950" x-text="getAssignedClient().name"></p>
                <p class="text-xs text-emerald-700" x-text="getAssignedClient().email"></p>

                <template x-if="assignedClientPassword">
                    <div class="mt-2 pt-2 border-t border-emerald-200 space-y-1.5">
                        <p class="text-xs text-emerald-700 font-medium">Contraseña temporal:</p>
                        <div class="flex items-center gap-1.5">
                            <code class="text-xs font-mono bg-emerald-100 px-2 py-1 rounded flex-1 text-emerald-900 break-all" x-text="assignedClientPassword"></code>
                            <button type="button" @click="copyToClipboard(assignedClientPassword)" class="px-2.5 py-1 rounded bg-emerald-600 text-white text-xs hover:bg-emerald-700 flex-shrink-0 font-semibold">
                                Copiar
                            </button>
                        </div>
                        <p class="text-[11px] text-emerald-600">Debe cambiarla al primer inicio de sesión.</p>
                    </div>
                </template>
            </div>
        </template>
    </section>

    <!-- Alta rápida de cliente -->
    <section class="admin-card p-3 space-y-2">
        <div class="flex items-center justify-between gap-2 mb-1">
            <p class="admin-eyebrow mb-0">Crear Cliente</p>
            <span class="text-[11px] uppercase tracking-wider text-stone-400">Opcional</span>
        </div>

        <div class="grid gap-2 grid-cols-2">
            <div>
                <label class="admin-label">Nombre</label>
                <input type="text" x-model="newClient.name" class="admin-input" placeholder="Nombre">
            </div>
            <div>
                <label class="admin-label">Email</label>
                <input type="email" x-model="newClient.email" class="admin-input" placeholder="correo@email.com">
            </div>
        </div>

        <button type="button" @click="createClient()" class="admin-link-button text-xs" :disabled="clientCreating">
            <span x-text="clientCreating ? 'Creando...' : '+ Crear y asignar'"></span>
        </button>
    </section>

    <!-- Estado de la invitación -->
    <section class="admin-card p-3 space-y-2">
        <p class="admin-eyebrow mb-1">Estado</p>

        <div x-data="{ open: false }" class="admin-accordion">
            <button type="button" @click="open = !open" class="admin-accordion-trigger">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-stone-900 text-left">Estado de publicación</p>
                    <p class="text-xs text-stone-500 text-left truncate" x-text="getStatusLabel()"></p>
                </div>
                <svg class="w-4 h-4 flex-shrink-0 transition-transform text-stone-500" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                </svg>
            </button>
            <div x-show="open" class="admin-accordion-panel space-y-1">
                <template x-for="(label, value) in statusLabels" :key="value">
                    <button type="button"
                        @click="meta.status = value; open = false"
                        class="admin-accordion-option"
                        :class="meta.status === value ? 'is-selected' : ''">
                        <span x-text="label"></span>
                    </button>
                </template>
            </div>
        </div>
    </section>
</div>
