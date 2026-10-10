{{--
    Lienzo: la invitación manda. Al tocar una sección dentro de la vista previa se abre su panel
    (script: setupPreviewEditing). Arriba, el estado y el tamaño del aparato; en el celular ocupa
    toda la zona sobre la hoja de edición. Estilos: admin.css («Editor»).
--}}
<div class="ed-preview min-w-0 flex-1 flex-col bg-site-surface">
    <div class="ed-preview__bar">
        <p class="flex items-center gap-2.5 text-sm font-medium" aria-live="polite">
            <span class="relative flex size-2">
                <span class="absolute inline-flex size-full animate-ping rounded-full bg-site-accent opacity-60" x-show="previewLoading"></span>
                <span class="relative inline-flex size-2 rounded-full bg-site-accent"></span>
            </span>
            <span x-text="previewLoading ? 'Actualizando' : 'Vista previa'">Vista previa</span>
        </p>
        <div class="ed-device-switch" role="radiogroup" aria-label="Tamaño de la vista previa">
            <template x-for="option in [{ id: 'phone', label: 'Celular' }, { id: 'tablet', label: 'Tableta' }, { id: 'desktop', label: 'Computadora' }]" :key="option.id">
                <button type="button" role="radio" :aria-checked="(previewDevice === option.id).toString()"
                    :class="{ 'is-on': previewDevice === option.id }" @click="previewDevice = option.id" x-text="option.label"></button>
            </template>
        </div>
        <span class="ed-preview__spacer" aria-hidden="true"></span>
    </div>
    <div class="ed-preview__stage">
        <div class="adm-device mx-auto h-full w-full" :style="`max-width:${previewWidth}px`">
            <iframe x-ref="previewFrame" src="about:blank" class="size-full rounded-[1.6rem] bg-white" title="Vista previa de la invitación"></iframe>
        </div>
    </div>
    <p class="ed-preview__hint">Toca cualquier sección de la invitación para editarla</p>
</div>
