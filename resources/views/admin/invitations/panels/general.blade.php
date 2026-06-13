<div x-show="activeTab === 'general'" x-cloak class="space-y-4">
    <section class="admin-card space-y-4">
        <div>
            <p class="admin-eyebrow">Datos generales</p>
            <h2 class="font-serif text-xl text-stone-950">Identidad de la invitación</h2>
            <p class="mt-1 text-sm leading-relaxed text-stone-500">
                Configura la información base del evento. Los módulos visibles se activan desde el panel lateral, organizados por apartado.
            </p>
        </div>

        <div class="rounded-2xl border border-stone-200 bg-stone-50 p-4">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="admin-label mb-1">Módulos activos</p>
                    <p class="text-xs text-stone-500">Usa el menú lateral para elegir el apartado y activar o editar cada feature.</p>
                </div>
                <span class="admin-editor-badge" x-text="`${activeModulesCount} activos`"></span>
            </div>
        </div>

        <div class="grid gap-4">
            <div>
                <label class="admin-label">Título del evento</label>
                <input type="text" name="title" x-model="meta.title" @input="onTitleInput()" class="admin-input" required>
            </div>
            <div>
                <label class="admin-label">Slug (URL pública)</label>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-stone-400 shrink-0">/p/</span>
                    <input type="text" name="slug" x-model="meta.slug" @input="slugManual = true" class="admin-input" required>
                </div>
            </div>
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                <div>
                    <label class="admin-label">Tipo de evento</label>
                    <select name="event_type_id" x-model="meta.event_type_id" class="admin-input" required>
                        @foreach($eventTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="admin-label">Cliente asignado</label>
                <select name="user_id" x-model="meta.user_id" class="admin-input">
                    <option value="">Sin cliente</option>
                    <template x-for="client in clients" :key="client.id">
                        <option :value="client.id" x-text="`${client.name} (${client.email})`"></option>
                    </template>
                </select>
            </div>
            <div class="rounded-2xl border border-stone-200 bg-stone-50 p-4">
                <div class="flex items-center justify-between gap-3 mb-3">
                    <div>
                        <p class="admin-label mb-1">Alta rápida de cliente</p>
                        <p class="text-xs text-stone-500">Crea y asigna al cliente sin abandonar la invitación.</p>
                    </div>
                    <span class="text-[10px] uppercase tracking-wider text-stone-400">Opcional</span>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="admin-label">Nombre</label>
                        <input type="text" x-model="newClient.name" class="admin-input" placeholder="Nombre del cliente">
                    </div>
                    <div>
                        <label class="admin-label">Email</label>
                        <input type="email" x-model="newClient.email" class="admin-input" placeholder="cliente@correo.com">
                    </div>
                </div>
                <div class="mt-3 flex items-center gap-3">
                    <button type="button" @click="createClient()" class="admin-link-button" :disabled="clientCreating">
                        <span x-text="clientCreating ? 'Creando...' : 'Crear y asignar'"></span>
                    </button>
                    <span class="text-xs text-stone-400">Se genera una contraseña automática.</span>
                </div>
            </div>
            <div>
                <label class="admin-label">Plantilla</label>
                <select name="template" x-model="meta.template" class="admin-input" required>
                    @foreach($templates as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                <div>
                    <label class="admin-label">Fecha del evento</label>
                    <input type="datetime-local" name="event_date" x-model="meta.event_date" class="admin-input" required>
                </div>
                <div>
                    <label class="admin-label">Expira el</label>
                    <input type="date" name="expires_at" x-model="meta.expires_at" class="admin-input" required>
                </div>
            </div>
            <div>
                <label class="admin-label">Estado</label>
                <select name="status" x-model="meta.status" class="admin-input">
                    @foreach(['draft' => 'Borrador', 'active' => 'Activa', 'suspended' => 'Suspendida', 'expired' => 'Expirada'] as $val => $lbl)
                        <option value="{{ $val }}">{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </section>
</div>
