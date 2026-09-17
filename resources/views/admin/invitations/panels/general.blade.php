<div x-show="activeTab === 'general'" x-cloak class="space-y-4">
    @include('admin.partials.panel-intro', [
        'eyebrow' => 'General',
        'title' => 'Datos del evento',
        'description' => 'el título, la fecha y la hora que aparecen en la invitación y en la cuenta regresiva.',
    ])

    {{-- Producto: invitación a un evento o tarjeta de temporada (cambia plantillas, pestañas y campos) --}}
    <section class="admin-card space-y-3 p-4">
        <h3 class="text-sm font-semibold">¿Qué vas a crear?</h3>
        <div class="grid grid-cols-2 gap-2" role="radiogroup" aria-label="Tipo de producto">
            <template x-for="kind in [{ value: 'invitation', label: 'Invitación', hint: 'Boda, XV, bautizo, cumpleaños' }, { value: 'card', label: 'Tarjeta', hint: 'Día del Amor y otras fechas' }]" :key="kind.value">
                <button type="button" role="radio" @click="chooseKind(kind.value)"
                    :aria-checked="((profile.kind ?? 'invitation') === kind.value).toString()"
                    :disabled="templatesOfKind(kind.value).length === 0"
                    class="rounded-[12px] border p-3 text-left transition-colors disabled:opacity-50"
                    :class="(profile.kind ?? 'invitation') === kind.value ? 'border-site-ink bg-site-bg' : 'border-site-line hover:bg-site-bg'">
                    <span class="block text-sm font-semibold" x-text="kind.label"></span>
                    <span class="mt-0.5 block text-xs text-site-muted" x-text="kind.hint"></span>
                </button>
            </template>
        </div>
    </section>

    {{-- Identidad --}}
    <section class="admin-card space-y-4 p-4">
        <div>
            <label for="invitation-title" class="admin-label">Título del evento</label>
            <input id="invitation-title" type="text" x-model="meta.title" @input="onTitleInput()" class="admin-input" placeholder="Boda de Ana y Luis" required>
        </div>
        <div>
            <label for="invitation-slug" class="admin-label">Dirección de la invitación</label>
            <div class="flex items-center gap-2">
                <span class="shrink-0 font-mono text-sm text-site-muted">/p/</span>
                <input id="invitation-slug" type="text" x-model="meta.slug" @input="slugManual = true" class="admin-input font-mono" required>
            </div>
            <p class="mt-1.5 text-xs text-site-muted">Es el enlace que vas a compartir. Usa minúsculas y guiones.</p>
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            <div x-data="{ open: false }" class="admin-accordion">
                <button type="button" @click="open = !open" class="admin-accordion-trigger">
                    <span class="min-w-0 text-left">
                        <span class="block text-xs text-site-muted">Tipo de evento</span>
                        <span class="block truncate text-sm font-semibold" x-text="getEventTypeName()"></span>
                    </span>
                    <x-phosphor-caret-down class="size-4 shrink-0 text-site-muted" x-bind:class="{ 'rotate-180': open }" aria-hidden="true" />
                </button>
                <div x-show="open" x-cloak class="admin-accordion-panel">
                    <template x-for="type in eventTypes.filter(item => (item.kind ?? 'invitation') === (profile.kind ?? 'invitation'))" :key="type.id">
                        <button type="button" @click="meta.event_type_id = type.id; open = false"
                            class="admin-accordion-option" :class="String(meta.event_type_id) === String(type.id) ? 'is-selected' : ''">
                            <span x-text="type.name"></span>
                        </button>
                    </template>
                </div>
            </div>

            <div x-data="{ open: false }" class="admin-accordion">
                <button type="button" @click="open = !open" class="admin-accordion-trigger">
                    <span class="min-w-0 text-left">
                        <span class="block text-xs text-site-muted">Plantilla</span>
                        <span class="block truncate text-sm font-semibold" x-text="getTemplateLabel()"></span>
                    </span>
                    <x-phosphor-caret-down class="size-4 shrink-0 text-site-muted" x-bind:class="{ 'rotate-180': open }" aria-hidden="true" />
                </button>
                <div x-show="open" x-cloak class="admin-accordion-panel">
                    <template x-for="option in templatesOfKind(profile.kind ?? 'invitation')" :key="option.value">
                        <button type="button" @click="meta.template = option.value; open = false"
                            class="admin-accordion-option" :class="meta.template === option.value ? 'is-selected' : ''">
                            <span class="min-w-0 text-left">
                                <span class="block" x-text="option.label"></span>
                                <span class="block text-xs font-normal text-site-muted" x-text="option.description"></span>
                            </span>
                        </button>
                    </template>
                </div>
            </div>
        </div>
    </section>

    {{-- Fecha y hora --}}
    <section class="admin-card space-y-4 p-4">
        <h3 class="text-sm font-semibold">Fecha y hora</h3>
        <div class="grid gap-4 sm:grid-cols-[minmax(0,1fr)_9rem]">
            @include('admin.partials.date-field', ['id' => 'event-date', 'label' => 'Día del evento', 'model' => 'eventDatePart'])
            <div class="admin-date-field" x-data="timeField()" x-modelable="value" x-model="eventTimePart">
                <label for="event-time" class="admin-label">Hora</label>
                <input id="event-time" type="text" inputmode="numeric" placeholder="HH:MM" maxlength="5" autocomplete="off" required
                    class="admin-input font-mono tracking-wide"
                    :style="error ? 'border-color: var(--site-danger)' : ''"
                    :aria-invalid="error ? 'true' : 'false'" aria-describedby="event-time-help"
                    :value="text" @input="onInput($event)" @blur="onBlur()">
                <p id="event-time-help" class="text-xs" :class="error ? 'text-site-danger' : 'text-site-muted'"
                    x-text="error || 'En 24 horas, por ejemplo 18:30'">En 24 horas, por ejemplo 18:30</p>
            </div>
        </div>
        @include('admin.partials.date-field', ['id' => 'expires-at', 'label' => 'El enlace deja de funcionar el', 'model' => 'meta.expires_at'])
    </section>

    {{-- Cliente --}}
    <section class="admin-card space-y-4 p-4">
        <div>
            <h3 class="text-sm font-semibold">Cliente</h3>
            <p class="mt-0.5 text-xs text-site-muted">Con su usuario y contraseña ve las confirmaciones y descarga sus reportes.</p>
        </div>

        <div x-data="{ open: false }" class="admin-accordion">
            <button type="button" @click="open = !open" class="admin-accordion-trigger">
                <span class="min-w-0 text-left">
                    <span class="block truncate text-sm font-semibold" x-text="getAssignedClient()?.name ?? 'Sin cliente asignado'"></span>
                    <span class="block truncate text-xs text-site-muted" x-text="getAssignedClient() ? `Usuario: ${getAssignedClient().username}` : 'Elige un cliente o crea uno nuevo abajo'"></span>
                </span>
                <x-phosphor-caret-down class="size-4 shrink-0 text-site-muted" x-bind:class="{ 'rotate-180': open }" aria-hidden="true" />
            </button>
            <div x-show="open" x-cloak class="admin-accordion-panel">
                <button type="button" @click="meta.user_id = ''; open = false" class="admin-accordion-option" :class="!meta.user_id ? 'is-selected' : ''">
                    Sin cliente asignado
                </button>
                <template x-for="client in clients" :key="client.id">
                    <button type="button" @click="meta.user_id = client.id; open = false"
                        class="admin-accordion-option" :class="String(meta.user_id) === String(client.id) ? 'is-selected' : ''">
                        <span class="block font-medium" x-text="client.name"></span>
                        <span class="block text-xs font-normal text-site-muted" x-text="`Usuario: ${client.username}`"></span>
                    </button>
                </template>
            </div>
        </div>

        {{-- Datos de acceso del cliente asignado (solo los ve el administrador) --}}
        <template x-if="getAssignedClient()">
            <div class="rounded-[12px] bg-site-tint p-3" x-data="{ showPassword: false, copied: false }">
                <p class="flex items-center gap-1.5 text-xs font-medium text-site-muted">
                    <x-phosphor-key class="size-4" aria-hidden="true" />
                    Datos de acceso
                </p>
                <dl class="mt-2 divide-y divide-site-line">
                    <div class="flex items-center justify-between gap-3 py-1.5">
                        <dt class="text-sm text-site-muted">Usuario</dt>
                        <dd class="flex min-w-0 items-center gap-1">
                            <code class="truncate font-mono text-sm font-semibold" x-text="getAssignedClient().username"></code>
                            <button type="button" class="admin-icon-button" @click="copyToClipboard(getAssignedClient().username)" aria-label="Copiar usuario" title="Copiar usuario">
                                <x-phosphor-copy aria-hidden="true" />
                            </button>
                        </dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 py-1.5">
                        <dt class="text-sm text-site-muted">Contraseña</dt>
                        <dd class="flex min-w-0 items-center gap-1">
                            <template x-if="getAssignedClient().password">
                                <span class="flex items-center gap-1">
                                    <code class="font-mono text-sm font-semibold" x-text="showPassword ? getAssignedClient().password : '••••-••••'"></code>
                                    <button type="button" class="admin-icon-button" @click="showPassword = !showPassword"
                                        :aria-label="showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'" :title="showPassword ? 'Ocultar' : 'Mostrar'">
                                        <x-phosphor-eye x-show="!showPassword" aria-hidden="true" />
                                        <x-phosphor-eye-slash x-show="showPassword" x-cloak aria-hidden="true" />
                                    </button>
                                    <button type="button" class="admin-icon-button" @click="copyToClipboard(getAssignedClient().password)" aria-label="Copiar contraseña" title="Copiar contraseña">
                                        <x-phosphor-copy aria-hidden="true" />
                                    </button>
                                </span>
                            </template>
                            <template x-if="!getAssignedClient().password">
                                <span class="flex flex-col items-end gap-1">
                                    <span class="text-right text-xs text-site-muted">Guardada cifrada: solo se ve al crearla</span>
                                    <button type="button" class="admin-link-button" :disabled="clientPasswordLoading"
                                        @click="regenerateClientPassword(getAssignedClient())">
                                        <span x-text="clientPasswordLoading ? 'Generando…' : 'Generar una nueva'">Generar una nueva</span>
                                    </button>
                                </span>
                            </template>
                        </dd>
                    </div>
                </dl>
                <button type="button" class="admin-link-button mt-3 w-full"
                    @click="copyToClipboard(clientAccessMessage(getAssignedClient())); copied = true; setTimeout(() => copied = false, 2000)">
                    <x-phosphor-check x-show="copied" x-cloak aria-hidden="true" />
                    <x-phosphor-whatsapp-logo x-show="!copied" aria-hidden="true" />
                    <span x-text="copied ? 'Mensaje copiado' : 'Copiar mensaje para enviar'">Copiar mensaje para enviar</span>
                </button>
            </div>
        </template>

        {{-- Alta rápida: solo el nombre --}}
        <div class="border-t border-site-line pt-4">
            <label for="new-client-name" class="admin-label">Crear cliente nuevo</label>
            <div class="flex gap-2">
                <input id="new-client-name" type="text" x-model="newClient.name" @keydown.enter.prevent="createClient()"
                    class="admin-input" placeholder="Nombre y apellido" autocomplete="off">
                <button type="button" @click="createClient()" class="admin-primary-button shrink-0" :disabled="clientCreating">
                    <x-phosphor-user-plus aria-hidden="true" />
                    <span x-text="clientCreating ? 'Creando' : 'Crear'">Crear</span>
                </button>
            </div>
            <p class="mt-1.5 text-xs" :class="clientError ? 'text-site-danger' : 'text-site-muted'"
                x-text="clientError || 'Generamos su usuario y contraseña, y queda asignado a esta invitación.'"></p>
        </div>
    </section>

    {{-- Publicación --}}
    <section class="admin-card space-y-3 p-4">
        <h3 class="text-sm font-semibold">Publicación</h3>
        <div class="grid grid-cols-2 gap-2" role="radiogroup" aria-label="Estado de la invitación">
            <button type="button" role="radio" @click="meta.status = 'active'" :aria-checked="(meta.status === 'active').toString()"
                class="rounded-[12px] border p-3 text-left transition-colors"
                :class="meta.status === 'active' ? 'border-site-ink bg-site-bg' : 'border-site-line hover:bg-site-bg'">
                <span class="flex items-center gap-2 text-sm font-semibold">
                    <x-phosphor-globe-simple class="size-4 text-site-accent" aria-hidden="true" />
                    Activa
                </span>
                <span class="mt-1 block text-xs text-site-muted">Los invitados pueden abrir el enlace.</span>
            </button>
            <button type="button" role="radio" @click="meta.status = 'inactive'" :aria-checked="(meta.status === 'inactive').toString()"
                class="rounded-[12px] border p-3 text-left transition-colors"
                :class="meta.status === 'inactive' ? 'border-site-ink bg-site-bg' : 'border-site-line hover:bg-site-bg'">
                <span class="flex items-center gap-2 text-sm font-semibold">
                    <x-phosphor-eye-slash class="size-4 text-site-muted" aria-hidden="true" />
                    Inactiva
                </span>
                <span class="mt-1 block text-xs text-site-muted">El enlace no se muestra. Puedes seguir editándola.</span>
            </button>
        </div>
    </section>
</div>
