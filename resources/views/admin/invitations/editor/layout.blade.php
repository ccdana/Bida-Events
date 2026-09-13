<div class="admin-editor-shell flex h-full flex-col" x-data="invitationForm(@js($editorConfig))" x-init="init()"
    :style="`--admin-primary:${modules.config?.colores?.primary || '#C9A96E'};--admin-secondary:${modules.config?.colores?.secondary || '#2C1810'};--admin-accent:${modules.config?.colores?.accent || '#F5E6D3'};--admin-text:${modules.config?.colores?.text || '#1A1A1A'};--admin-bg:${modules.config?.colores?.background || '#FFFAF5'}`">
    @unless($cloudinaryConfigured)
        <p class="flex shrink-0 items-center justify-center gap-2 border-b border-site-line bg-site-tint px-4 py-2 text-center text-sm">
            <x-phosphor-cloud-slash class="size-4 shrink-0 text-site-accent" aria-hidden="true" />
            <span>Cloudinary no está configurado: los archivos se guardarán en el almacenamiento local. Agrega <code class="font-mono text-xs">CLOUDINARY_URL</code> en tu .env.</span>
        </p>
    @endunless

    <div class="flex min-h-0 flex-1">
        @include('admin.invitations.editor.sidebar')
        @include('admin.invitations.editor.preview')
    </div>

    {{-- Recorte de imágenes --}}
    <div x-show="cropperOpen" x-cloak
        class="admin-crop-backdrop"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click.self="closeImageCropper()"
        @mousemove.window="onCropDrag($event)"
        @mouseup.window="endCropDrag()"
        @touchmove.window="onCropDrag($event)"
        @touchend.window="endCropDrag()">

        <div class="admin-crop-panel relative flex flex-col" role="dialog" aria-modal="true" aria-labelledby="cropper-title"
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="opacity-0 translate-y-8 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-8 scale-95">

            <button type="button" @click="closeImageCropper()" class="admin-icon-button absolute right-3 top-3" aria-label="Cerrar">
                <x-phosphor-x aria-hidden="true" />
            </button>

            <div class="mb-5 pr-10">
                <h3 id="cropper-title" class="text-lg font-semibold tracking-tight">Ajustar imagen</h3>
                <p class="mt-1 text-sm text-site-muted">Arrastra la imagen para encuadrarla y usa el control o la rueda del mouse para acercar.</p>
            </div>

            <div x-ref="cropperFrame"
                class="admin-crop-frame cursor-grab active:cursor-grabbing"
                :style="`aspect-ratio:${cropperAspect}`"
                @mousedown.prevent="startCropDrag($event)"
                @touchstart.prevent="startCropDrag($event)"
                @wheel.prevent="onCropWheel($event)">
                <img :src="cropperBlobUrl"
                    alt=""
                    class="admin-crop-image pointer-events-none max-h-none max-w-none"
                    :style="cropperImageStyle()"
                    @load="onCropperImageLoad($event)">

                <div class="pointer-events-none absolute inset-0 grid grid-cols-3 grid-rows-3 opacity-30 mix-blend-overlay" aria-hidden="true">
                    <div class="border-b border-r border-white"></div>
                    <div class="border-b border-r border-white"></div>
                    <div class="border-b border-white"></div>
                    <div class="border-b border-r border-white"></div>
                    <div class="border-b border-r border-white"></div>
                    <div class="border-b border-white"></div>
                    <div class="border-r border-white"></div>
                    <div class="border-r border-white"></div>
                    <div></div>
                </div>
            </div>

            <div class="mt-5 flex items-center gap-2">
                <button type="button" @click="cropperScale = Math.max(cropperMinScale, cropperScale - 0.1)" class="admin-icon-button" aria-label="Alejar">
                    <x-phosphor-minus aria-hidden="true" />
                </button>
                <input type="range" min="1" max="3" step="0.01" x-model.number="cropperScale" class="adm-range flex-1" aria-label="Zoom">
                <button type="button" @click="cropperScale = Math.min(cropperMaxScale, cropperScale + 0.1)" class="admin-icon-button" aria-label="Acercar">
                    <x-phosphor-plus aria-hidden="true" />
                </button>
                <span class="w-12 text-right text-sm tabular-nums text-site-muted" x-text="Math.round(cropperScale * 100) + '%'"></span>
            </div>

            <div class="mt-6 flex flex-col-reverse items-center justify-between gap-3 sm:flex-row">
                <button type="button"
                    @click="cropperScale = 1; cropperOffsetX = 0; cropperOffsetY = 0;"
                    class="admin-link-button w-full sm:w-auto">
                    <x-phosphor-arrow-counter-clockwise aria-hidden="true" />
                    Restablecer
                </button>

                <div class="flex w-full gap-2 sm:w-auto">
                    <button type="button" @click="closeImageCropper()" class="admin-link-button flex-1 sm:flex-none">Cancelar</button>
                    <button type="button" @click="applyImageCrop()" class="admin-primary-button flex-1 sm:flex-none">
                        <x-phosphor-check aria-hidden="true" />
                        Aplicar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
