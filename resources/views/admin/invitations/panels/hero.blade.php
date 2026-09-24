<div x-show="activeTab === 'hero'" x-cloak class="space-y-2">
    @include('admin.partials.panel-intro', [
        'eyebrow' => 'Banner principal',
        'title' => 'Portada de la invitación',
        'description' => 'es lo primero que aparece al abrir el enlace: subtítulo, nombre en letra manuscrita, mensaje y fecha sobre la foto de fondo.',
        'tip' => 'Usa una foto vertical con el rostro en la parte superior: el texto se ubica al centro y la imagen se oscurece un poco para que se lea bien.',
        'moduleKey' => 'bienvenida',
    ])

    <!-- Identidad -->
    <section class="admin-card p-3 space-y-2">
        <p class="admin-eyebrow mb-1">Identidad</p>

        <div class="grid gap-2">
            {{-- Cada evento pide sus campos (App\EventProfiles): dos nombres en una boda, la edad en un cumpleaños --}}
            <template x-for="field in heroFields" :key="field.key">
                <div>
                    <label class="admin-label" :for="'hero-' + field.key" x-text="field.label"></label>
                    <input :id="'hero-' + field.key" :type="field.type || 'text'" class="admin-input"
                        :min="field.type === 'number' ? 1 : null" :max="field.type === 'number' ? 120 : null"
                        :value="modules.bienvenida[field.key] ?? ''"
                        @input="modules.bienvenida[field.key] = $event.target.value; syncHeroName()"
                        :placeholder="field.placeholder">
                    <p x-show="field.help" x-cloak class="mt-1 text-xs text-site-muted" x-text="field.help"></p>
                </div>
            </template>
            <div>
                <label class="admin-label" for="hero-subtitulo" x-text="isCard ? 'Frase de la portada' : 'Subtítulo superior'">Subtítulo superior</label>
                <input id="hero-subtitulo" type="text" x-model="modules.bienvenida.subtitulo" @input="schedulePreview()" class="admin-input" :placeholder="'Ej. ' + (profile.sample?.subtitle ?? 'Celebrando mis XV años')">
            </div>
        </div>
    </section>

    <!-- Mensaje de bienvenida -->
    <section class="admin-card p-3 space-y-2">
        <div x-data="{ open: true }" class="admin-accordion">
            <button type="button" @click="open = !open" class="admin-accordion-trigger">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-stone-900 text-left">Mensaje de bienvenida</p>
                    <p class="text-xs text-stone-500 text-left truncate" x-text="modules.bienvenida.mensaje || 'Texto principal para tus invitados'"></p>
                </div>
                <x-phosphor-caret-down class="w-4 h-4 flex-shrink-0 transition-transform text-stone-500" x-bind:class="{ 'rotate-180': open }" aria-hidden="true" />
            </button>
            <div x-show="open" class="admin-accordion-panel">
                <textarea x-model="modules.bienvenida.mensaje" @input="schedulePreview()" rows="4" class="admin-input" placeholder="Escribe el mensaje principal que verán tus invitados."></textarea>
            </div>
        </div>
    </section>

    <!-- Fecha visible -->
    <section class="admin-card p-3 space-y-2" x-show="!isCard">
        <p class="admin-eyebrow mb-1">Fecha visible</p>
        <div>
            <label class="admin-label">Texto de fecha en el banner</label>
            <input type="text" x-model="modules.bienvenida.fecha_texto" @input="schedulePreview()" class="admin-input" placeholder="Ej. Sábado 15 de Noviembre, 2026">
            <p class="mt-1.5 text-xs text-stone-500">Este texto se muestra en la portada. Puede diferir del formato de la fecha del evento.</p>
        </div>
    </section>

    <!-- Mensaje post-evento -->
    <section class="admin-card p-3 space-y-2" x-show="!isCard">
        <div x-data="{ open: false }" class="admin-accordion">
            <button type="button" @click="open = !open" class="admin-accordion-trigger">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-stone-900 text-left">Mensaje post-evento</p>
                    <p class="text-xs text-stone-500 text-left truncate" x-text="modules.bienvenida.mensaje_post_evento || 'Opcional · agradecimiento después del evento'"></p>
                </div>
                <x-phosphor-caret-down class="w-4 h-4 flex-shrink-0 transition-transform text-stone-500" x-bind:class="{ 'rotate-180': open }" aria-hidden="true" />
            </button>
            <div x-show="open" class="admin-accordion-panel">
                <textarea x-model="modules.bienvenida.mensaje_post_evento" @input="schedulePreview()" rows="3" class="admin-input" placeholder="Mensaje de agradecimiento para después del evento."></textarea>
            </div>
        </div>
    </section>

    <!-- Imagen del banner -->
    <section class="admin-card p-3 space-y-2">
        @include('admin.partials.cloudinary-upload', [
            'label' => 'Imagen del banner',
            'type' => 'image',
            'context' => 'hero',
            'accept' => 'image/jpeg,image/png,image/webp',
            'previewExpr' => 'modules.bienvenida.imagen_hero',
        ])
        <p class="text-xs text-stone-500">Al elegirla se abre el encuadre con la forma y la medida del espacio de esta plantilla. Mejor en alta resolución (JPG, PNG o WebP).</p>
    </section>
</div>
