{{--
    Textos de la sección abierta: el título, la frase de arriba, los botones y lo que se ve cuando
    todavía no hay datos. Va debajo del panel de cada módulo (en «General», los de toda la
    invitación). Un campo vacío muestra el texto de la plantilla, que se ve como ejemplo.
    Catálogo: App\Support\EditableTexts.
--}}
<section x-show="activeTextFields().length > 0" x-cloak class="admin-card space-y-3 p-4" x-data="{ textsOpen: false }">
    <button type="button" @click="textsOpen = !textsOpen" class="flex w-full items-start justify-between gap-3 text-left"
        :aria-expanded="textsOpen.toString()">
        <span class="min-w-0">
            <span class="admin-eyebrow block">Textos</span>
            <span class="block text-base font-semibold tracking-tight"
                x-text="activeTab === 'general' ? 'Textos generales de la invitación' : 'Textos de esta sección'"></span>
            <span class="mt-1 block text-sm leading-relaxed text-site-muted">
                Cambia cualquier título, frase o botón. Si dejas un campo vacío se usa el de la plantilla.
            </span>
        </span>
        <span class="flex shrink-0 items-center gap-1.5">
            <span class="admin-status-badge" x-show="customTextsCount() > 0" :class="'is-active'"
                x-text="customTextsCount() === 1 ? '1 cambiado' : `${customTextsCount()} cambiados`"></span>
            <x-phosphor-caret-down class="size-4 text-site-muted transition-transform" ::class="textsOpen ? 'rotate-180' : ''" aria-hidden="true" />
        </span>
    </button>

    <div x-show="textsOpen" class="space-y-3">
        <template x-for="field in activeTextFields()" :key="meta.template + field.key">
            <div>
                <label class="admin-label" :for="`texto-${field.key}`" x-text="field.label"></label>
                <template x-if="field.long">
                    <textarea :id="`texto-${field.key}`" rows="3" maxlength="600" class="admin-input"
                        :placeholder="field.default" x-model="modules.config.textos[field.key]"></textarea>
                </template>
                <template x-if="!field.long">
                    <input type="text" :id="`texto-${field.key}`" maxlength="600" class="admin-input"
                        :placeholder="field.default" x-model="modules.config.textos[field.key]">
                </template>
            </div>
        </template>

        <div class="flex items-center justify-between gap-3">
            <p class="text-xs text-site-muted">Los cambios se ven en la vista previa al instante.</p>
            <button type="button" class="admin-link-button text-xs" x-show="customTextsCount() > 0" @click="resetTexts()">
                <x-phosphor-arrow-counter-clockwise aria-hidden="true" />
                Volver a los de la plantilla
            </button>
        </div>
    </div>
</section>
