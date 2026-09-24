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

    {{--
        Recortador: la foto se encuadra en el marco real del espacio donde va (proporción y forma de la
        plantilla, App\Support\ImageFrames). Lo que queda fuera se ve atenuado alrededor, para saber qué
        se pierde. Arrastrar mueve, la rueda o el pellizco acercan, las flechas mueven de a poco.
    --}}
    <div x-show="cropperOpen" x-cloak
        class="admin-crop-backdrop"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click.self="closeImageCropper()"
        @keydown.escape.window="cropperOpen && closeImageCropper()"
        @mousemove.window="onCropDrag($event)"
        @mouseup.window="endCropDrag()"
        @touchmove.window="onCropDrag($event)"
        @touchend.window="endCropDrag()">

        <div class="admin-crop-panel relative flex flex-col" role="dialog" aria-modal="true" aria-labelledby="cropper-title"
            x-transition:enter="transition ease-out duration-200 transform"
            x-transition:enter-start="opacity-0 translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0">

            <button type="button" @click="closeImageCropper()" class="admin-icon-button absolute right-3 top-3" aria-label="Cerrar">
                <x-phosphor-x aria-hidden="true" />
            </button>

            <div class="mb-4 pr-10">
                <h3 id="cropper-title" class="text-lg font-semibold tracking-tight">Encuadra la foto</h3>
                <p class="mt-0.5 text-sm text-site-muted">
                    <span class="font-medium text-site-ink" x-text="cropperFrame?.label ?? 'Foto'"></span>:
                    lo que ves dentro del marco es lo que se verá en la invitación.
                </p>
            </div>

            <div x-ref="cropperStage" class="admin-crop-stage" tabindex="0"
                aria-label="Área de recorte. Arrastra la foto, acerca con la rueda o pellizcando, o usa las flechas y las teclas más y menos."
                @mousedown.prevent="startCropDrag($event)"
                @touchstart.prevent="startCropDrag($event)"
                @wheel.prevent="onCropWheel($event)"
                @keydown="onCropKey($event)"
                @dblclick="cropperScale = cropperMinScale; cropperOffsetX = 0; cropperOffsetY = 0">
                <div x-ref="cropperFrame" class="admin-crop-frame" :class="['is-' + (cropperFrame?.shape ?? 'rect'), cropperDragging || cropperPinch ? 'is-moving' : '']"
                    :style="`width:${cropperFrameWidth}px;height:${cropperFrameHeight}px;--crop-w:${cropperFrameWidth}px`">
                    <img :src="cropperBlobUrl" alt="" class="admin-crop-image pointer-events-none max-h-none max-w-none"
                        :style="cropperImageStyle()" @load="onCropperImageLoad($event)" draggable="false">
                    {{-- Tercios: aparecen mientras se mueve, para alinear caras y horizontes --}}
                    <span class="admin-crop-thirds" aria-hidden="true"></span>
                </div>
                <p x-show="cropperLoading" class="admin-crop-loading">Abriendo la foto…</p>
            </div>

            {{-- Qué se guarda y si alcanza la calidad --}}
            <div class="mt-3 flex flex-wrap items-center justify-between gap-x-4 gap-y-1.5 text-xs">
                <p class="text-site-muted" x-show="cropperFrame">
                    <span class="font-medium capitalize text-site-ink" x-text="cropperFrame ? frameShapeLabel(cropperFrame) : ''"></span>
                    · se guarda en <span class="font-mono" x-text="cropperOutputSize() ? `${cropperOutputSize().width} × ${cropperOutputSize().height} px` : '—'"></span>
                </p>
                <p class="admin-crop-quality" :class="'is-' + (cropperQuality()?.level ?? 'good')" x-show="cropperQuality()" x-text="cropperQuality()?.text"></p>
            </div>

            <div class="mt-4 flex items-center gap-2">
                <button type="button" @click="cropperScale = Math.max(cropperMinScale, cropperScale - 0.1)" class="admin-icon-button" aria-label="Alejar">
                    <x-phosphor-magnifying-glass-minus aria-hidden="true" />
                </button>
                <input type="range" :min="cropperMinScale" :max="cropperMaxScale" step="0.01" x-model.number="cropperScale" class="adm-range flex-1" aria-label="Acercar o alejar">
                <button type="button" @click="cropperScale = Math.min(cropperMaxScale, cropperScale + 0.1)" class="admin-icon-button" aria-label="Acercar">
                    <x-phosphor-magnifying-glass-plus aria-hidden="true" />
                </button>
            </div>

            <div class="mt-5 flex flex-wrap items-center justify-between gap-2">
                <button type="button" @click="cropperScale = cropperMinScale; cropperOffsetX = 0; cropperOffsetY = 0" class="admin-link-button">
                    <x-phosphor-arrow-counter-clockwise aria-hidden="true" />
                    Centrar
                </button>

                <div class="ml-auto flex gap-2">
                    <button type="button" @click="closeImageCropper()" class="admin-link-button">Cancelar</button>
                    <button type="button" @click="applyImageCrop()" class="admin-primary-button">
                        <x-phosphor-check aria-hidden="true" />
                        Usar encuadre
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
