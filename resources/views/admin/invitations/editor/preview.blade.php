{{--
    La invitación es el lienzo («tocar y cambiar»): cada texto o foto se toca y se cambia en una burbuja
    junto a él (editor/panel); el botón negro «Cambiar …» de cada parte muestra todo lo de esa parte.
    Arriba, la barra de herramientas: las partes (celular), los datos del evento, los colores y letras,
    el tamaño del aparato, «Ver como invitado» y publicar. Estilos: admin.css («Editor: estilo Canva»).
--}}
<div class="ed-toolbar" role="toolbar" aria-label="Herramientas del editor">
    <button type="button" class="ed-tool ed-tool--parts" @click="modulesOpen = true">
        <x-phosphor-squares-four aria-hidden="true" />
        Partes
    </button>
    <button type="button" class="ed-tool" :class="{ 'is-on': panelMode === 'full' && activeTab === 'general' }" @click="openFull('general')">
        <x-phosphor-calendar-blank aria-hidden="true" />
        Datos del evento
    </button>
    <button type="button" class="ed-tool" :class="{ 'is-on': panelMode === 'full' && activeTab === 'estetica' }" @click="openFull('estetica')">
        <x-phosphor-palette aria-hidden="true" />
        Colores y letras
    </button>

    <div class="ed-device-switch" role="radiogroup" aria-label="Tamaño de la vista previa">
        <template x-for="option in [{ id: 'phone', label: 'Celular' }, { id: 'tablet', label: 'Tableta' }, { id: 'desktop', label: 'Computadora' }]" :key="option.id">
            <button type="button" role="radio" :aria-checked="(previewDevice === option.id).toString()"
                :class="{ 'is-on': previewDevice === option.id }" @click="previewDevice = option.id; $nextTick(() => placeBubble())" x-text="option.label"></button>
        </template>
    </div>

    <span class="ed-toolbar__status" aria-live="polite" x-text="previewLoading ? 'Actualizando…' : ''"></span>

    <button type="button" role="switch" class="ed-tool" :class="{ 'is-on': guestView }"
        :aria-checked="guestView.toString()" @click="guestView = !guestView">
        <x-phosphor-eye aria-hidden="true" />
        Ver como invitado
    </button>

    {{-- «Hazlo tú»: el nivel que piden las partes encendidas; al tocarlo, qué lo sube y cómo bajarlo --}}
    <button type="button" class="ed-tool ed-tool--tier" x-show="isDiy" x-cloak @click="tierDialog = true"
        :title="'Tu invitación hoy es nivel ' + diyTierName(diyRequiredTier) + '. Toca para ver por qué.'">
        <span class="ed-tool__muted">Nivel</span>
        <span x-text="diyTierName(diyRequiredTier)"></span>
    </button>

    <template x-if="config.isCreate">
        <button type="button" class="ed-tool ed-tool--primary" @click="document.getElementById('invitation-form').requestSubmit()" :disabled="mediaUploading">
            <x-phosphor-plus aria-hidden="true" />
            Crear invitación
        </button>
    </template>
    <template x-if="!config.isCreate">
        <span class="contents">
            <button type="button" class="ed-tool ed-tool--primary" x-show="!isPublished" @click="requestPublish()" :disabled="mediaUploading">
                <x-phosphor-paper-plane-tilt aria-hidden="true" />
                Publicar
            </button>
            {{-- «Hazlo tú» publicada con un nivel menor que el que piden sus partes: lo de más no se ve todavía --}}
            <button type="button" class="ed-tool ed-tool--primary" x-show="isPublished && diyNeedsUpgrade" x-cloak @click="publish()" :disabled="mediaUploading">
                <x-phosphor-arrow-circle-up aria-hidden="true" />
                <span x-text="'Subir a ' + diyTierName(diyRequiredTier)"></span>
            </button>
            <button type="button" class="ed-tool ed-tool--published" x-show="isPublished" :disabled="mediaUploading"
                @click="if (confirm('¿Ocultar la invitación? Tus invitados dejarán de verla hasta que la publiques otra vez.')) unpublish()"
                title="Está visible para tus invitados. Tócalo para ocultarla.">
                <x-phosphor-check-circle aria-hidden="true" />
                Publicada
            </button>
        </span>
    </template>
</div>

<div class="ed-preview">
    <p class="ed-preview__tip" x-show="!guestView && panelMode === 'closed'" x-cloak x-transition.opacity>
        Toca un <strong>texto</strong> o una <strong>foto</strong> para cambiarlo
    </p>
    <p class="ed-preview__tip ed-preview__tip--guest" x-show="guestView" x-cloak>
        Así la recibirán tus invitados. Para seguir cambiando cosas, apaga «Ver como invitado».
    </p>
    <div class="ed-preview__stage">
        <div class="adm-device mx-auto h-full w-full" :style="`max-width:${previewWidth}px`">
            <iframe x-ref="previewFrame" src="about:blank" class="size-full rounded-[1.6rem] bg-white" title="Vista previa de la invitación"></iframe>
        </div>
    </div>
</div>
