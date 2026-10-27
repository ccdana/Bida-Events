{{--
    La invitación es el lienzo («tocar y cambiar»): cada texto o foto se toca y se cambia en una burbuja
    junto a él (editor/panel); el botón negro «Cambiar …» de cada parte muestra todo lo de esa parte.
    La barra de herramientas es solo de íconos: en el celular va abajo (Partes, Datos, Colores, Ver, Nivel y
    Publicar); en computadora Partes, Datos y Colores están en el riel de la izquierda (editor/rail) y aquí
    quedan el tamaño del aparato, Ver como invitado y publicar. Estilos: admin.css («Editor: estilo Canva»).
--}}
<div class="ed-toolbar" role="toolbar" aria-label="Herramientas del editor">
    {{-- Celular: lo mismo que el riel de computadora --}}
    <button type="button" class="ed-tool ed-tool--phone" :class="{ 'is-on': modulesOpen }" @click="modulesOpen = true" title="Partes">
        <x-phosphor-squares-four-light aria-hidden="true" />
        <span class="ed-tool__label">Partes</span>
    </button>
    <button type="button" class="ed-tool ed-tool--phone" :class="{ 'is-on': panelMode === 'full' && activeTab === 'general' }" @click="toggleFull('general')" title="Datos del evento">
        <x-phosphor-calendar-blank-light aria-hidden="true" />
        <span class="ed-tool__label">Datos</span>
        <span class="sr-only">del evento</span>
    </button>
    <button type="button" class="ed-tool ed-tool--phone" :class="{ 'is-on': panelMode === 'full' && activeTab === 'estetica' }" @click="toggleFull('estetica')" title="Colores y letras">
        <x-phosphor-palette-light aria-hidden="true" />
        <span class="ed-tool__label">Colores</span>
        <span class="sr-only">y letras</span>
    </button>

    <span class="ed-toolbar__status" aria-live="polite" x-text="uploadStatus || previewError || (previewLoading ? 'Actualizando…' : '')"></span>

    <div class="ed-device-switch" role="radiogroup" aria-label="Tamaño de la vista previa">
        <template x-for="option in [{ id: 'phone', label: 'Celular', icon: 'device-mobile' }, { id: 'tablet', label: 'Tableta', icon: 'device-tablet' }, { id: 'desktop', label: 'Computadora', icon: 'desktop' }]" :key="option.id">
            <button type="button" role="radio" :aria-checked="(previewDevice === option.id).toString()" :aria-label="option.label" :title="option.label"
                :class="{ 'is-on': previewDevice === option.id }" @click="previewDevice = option.id; $nextTick(() => placeBubble())">
                <x-phosphor-device-mobile-light x-show="option.icon === 'device-mobile'" aria-hidden="true" />
                <x-phosphor-device-tablet-light x-show="option.icon === 'device-tablet'" aria-hidden="true" />
                <x-phosphor-desktop-light x-show="option.icon === 'desktop'" aria-hidden="true" />
            </button>
        </template>
    </div>

    <button type="button" role="switch" class="ed-tool" :class="{ 'is-on': guestView }"
        :aria-checked="guestView.toString()" @click="guestView = !guestView" title="Ver como invitado" aria-label="Ver como invitado">
        <x-phosphor-eye-light aria-hidden="true" />
        <span class="ed-tool__label">Ver</span>
    </button>

    {{-- «Hazlo tú»: el nivel que piden las partes encendidas (de 0 a 3 puntos); al tocarlo, qué lo sube y cómo bajarlo --}}
    <button type="button" class="ed-tool ed-tool--tier" x-show="isDiy" x-cloak @click="tierDialog = true"
        :title="'Nivel ' + diyTierName(diyRequiredTier) + '. Toca para ver por qué.'"
        :aria-label="'Tu invitación hoy es nivel ' + diyTierName(diyRequiredTier) + '. Toca para ver por qué.'">
        <span class="ed-dots" :data-level="diyTierIndex(diyRequiredTier)" aria-hidden="true"><i></i><i></i><i></i></span>
        <span class="ed-tool__label">Nivel</span>
    </button>

    <template x-if="config.isCreate">
        <button type="button" class="ed-tool ed-tool--primary" @click="document.getElementById('invitation-form').requestSubmit()" :disabled="mediaUploading" title="Crear invitación">
            <x-phosphor-plus-light aria-hidden="true" />
            <span class="ed-tool__label">Crear</span>
        </button>
    </template>
    <template x-if="!config.isCreate">
        <span class="contents">
            <button type="button" class="ed-tool ed-tool--primary" x-show="!isPublished" @click="requestPublish()" :disabled="mediaUploading" title="Publicar">
                <x-phosphor-paper-plane-tilt-light aria-hidden="true" />
                <span class="ed-tool__label">Publicar</span>
            </button>
            {{-- «Hazlo tú» publicada con un nivel menor que el que piden sus partes: lo de más no se ve todavía --}}
            <button type="button" class="ed-tool ed-tool--primary" x-show="isPublished && diyNeedsUpgrade" x-cloak @click="publish()" :disabled="mediaUploading"
                :title="'Subir a ' + diyTierName(diyRequiredTier)">
                <x-phosphor-arrow-circle-up-light aria-hidden="true" />
                <span class="ed-tool__label">Subir</span>
                <span class="sr-only" x-text="' a ' + diyTierName(diyRequiredTier)"></span>
            </button>
            <button type="button" class="ed-tool ed-tool--published" x-show="isPublished" :disabled="mediaUploading"
                @click="if (confirm('¿Ocultar la invitación? Tus invitados dejarán de verla hasta que la publiques otra vez.')) unpublish()"
                title="Publicada: la ven tus invitados. Tócalo para ocultarla.">
                <x-phosphor-check-circle-light aria-hidden="true" />
                <span class="ed-tool__label">Publicada</span>
            </button>
        </span>
    </template>
</div>

{{-- La vista previa se dibuja con una sola petición: este formulario manda lo que se arma y la invitación llega al marco --}}
<form x-ref="previewForm" method="POST" :action="previewRenderUrl" target="ed-preview-frame" class="hidden" aria-hidden="true">
    @csrf
    <input type="hidden" name="payload" x-ref="previewPayload">
</form>

<div class="ed-preview">
    {{-- Un consejo que sale solo hasta que se toca algo por primera vez --}}
    <p class="ed-preview__tip" x-show="tipOpen && !guestView && panelMode === 'closed'" x-cloak x-transition.opacity>
        <x-phosphor-hand-tap-light aria-hidden="true" />
        <span>Toca un <strong>texto</strong> o una <strong>foto</strong> para cambiarlo</span>
    </p>
    <p class="ed-preview__tip ed-preview__tip--guest" x-show="guestView" x-cloak>
        <x-phosphor-eye-light aria-hidden="true" />
        <span>Así la ven tus invitados</span>
    </p>
    <div class="ed-preview__stage">
        <div class="adm-device mx-auto h-full w-full" :style="`max-width:${previewWidth}px`">
            <iframe x-ref="previewFrame" name="ed-preview-frame" src="about:blank" class="size-full rounded-[1.6rem] bg-white" title="Vista previa de la invitación"></iframe>
        </div>
    </div>
</div>
