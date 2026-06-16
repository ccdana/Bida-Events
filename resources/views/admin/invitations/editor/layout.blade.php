<div class="flex flex-col h-full admin-editor-shell" x-data="invitationForm(@js($editorConfig))" x-init="init()"
    :style="`--admin-primary:${modules.config?.colores?.primary || '#C9A96E'};--admin-secondary:${modules.config?.colores?.secondary || '#2C1810'};--admin-accent:${modules.config?.colores?.accent || '#F5E6D3'};--admin-text:${modules.config?.colores?.text || '#1A1A1A'};--admin-bg:${modules.config?.colores?.background || '#FFFAF5'}`">
    @unless($cloudinaryConfigured)
        <div class="shrink-0 bg-amber-50 border-b border-amber-200 text-amber-900 px-4 py-2 text-xs text-center">
            Cloudinary no configurado - los archivos se guardaran en storage local al guardar la invitacion. Configura <code class="font-mono">CLOUDINARY_URL</code> en tu .env
        </div>
    @endunless

    <div class="flex flex-1 min-h-0">
        @include('admin.invitations.editor.sidebar')
        @include('admin.invitations.editor.preview')
    </div>

    <!-- Cropper de imágenes -->
    <div x-show="cropperOpen" x-cloak
        class="admin-crop-backdrop"
        @click.self="closeImageCropper()"
        @mousemove.window="onCropDrag($event)"
        @mouseup.window="endCropDrag()"
        @touchmove.window="onCropDrag($event)"
        @touchend.window="endCropDrag()">
        <div class="admin-crop-panel">
            <p class="text-xs font-semibold text-stone-800 mb-2">Ajustar recorte</p>
            <p class="text-[11px] text-stone-500 mb-3">
                Arrastra la imagen y usa el zoom para que se vea como en la tarjeta.
            </p>
            <div x-ref="cropperFrame" class="admin-crop-frame" :style="`aspect-ratio:${cropperAspect}`">
                <img :src="cropperBlobUrl"
                    alt=""
                    class="admin-crop-image"
                    :style="cropperImageStyle()"
                    @load="onCropperImageLoad($event)"
                    @mousedown.prevent="startCropDrag($event)"
                    @touchstart.prevent="startCropDrag($event)">
            </div>
            <div class="mt-3 flex items-center gap-2">
                <span class="text-[11px] text-stone-500">Zoom</span>
                <input type="range" min="1" max="3" step="0.01" x-model.number="cropperScale" @input="clampCropperOffsets()" class="flex-1">
                <button type="button"
                    @click="cropperScale = 1; cropperOffsetX = 0; cropperOffsetY = 0;"
                    class="px-2 py-1 rounded border border-stone-200 text-[11px] text-stone-600 hover:bg-stone-50">
                    Reset
                </button>
            </div>
            <div class="mt-4 flex justify-end gap-2">
                <button type="button"
                    @click="closeImageCropper()"
                    class="px-3 py-1.5 rounded-md border border-stone-200 text-[11px] font-medium text-stone-600 hover:bg-stone-50">
                    Cancelar
                </button>
                <button type="button"
                    @click="applyImageCrop()"
                    class="px-3 py-1.5 rounded-md bg-stone-900 text-[11px] font-semibold text-white hover:bg-stone-800">
                    Aplicar recorte
                </button>
            </div>
        </div>
    </div>
</div>
