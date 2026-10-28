<div class="admin-editor-shell flex h-full flex-col" x-data="invitationForm(@js($editorConfig))" x-init="init()"
    :style="`--admin-primary:${modules.config?.colores?.primary || '#C9A96E'};--admin-secondary:${modules.config?.colores?.secondary || '#2C1810'};--admin-accent:${modules.config?.colores?.accent || '#F5E6D3'};--admin-text:${modules.config?.colores?.text || '#1A1A1A'};--admin-bg:${modules.config?.colores?.background || '#FFFAF5'}`">
    @unless($cloudinaryConfigured)
        <p class="flex shrink-0 items-center justify-center gap-2 border-b border-site-line bg-site-tint px-4 py-2 text-center text-sm">
            <x-phosphor-cloud-slash class="size-4 shrink-0 text-site-accent" aria-hidden="true" />
            <span>Cloudinary no está configurado: los archivos se guardarán en el almacenamiento local. Agrega <code class="font-mono text-xs">CLOUDINARY_URL</code> en tu .env.</span>
        </p>
    @endunless

    {{--
        Editor estilo Canva: a la izquierda las partes que se encienden y apagan (modules); al centro la
        invitación como lienzo con su barra de herramientas (preview); al tocar un texto o una foto, una
        burbuja con solo ese campo (panel). Una invitación nueva empieza por el asistente (wizard).
    --}}
    <div class="ed-shell relative" :class="{ 'is-guest': guestView, 'is-editing': isEditingPart }">
        @include('admin.invitations.editor.rail')
        @include('admin.invitations.editor.modules')
        <div class="ed-canvas" x-ref="canvas" :class="{ 'has-bubble': isBubble }">
            @include('admin.invitations.editor.preview')
            @include('admin.invitations.editor.panel')
        </div>
        @include('admin.invitations.editor.wizard')
    </div>

    {{--
        Antes de publicar: lo que sigue de ejemplo y lo que falta, con atajo a cada parte. Cada renglón es un botón entero
        (nombre, cuánto falta y una flecha); sin párrafos. En el celular sube desde abajo como una hoja.
    --}}
    <div class="ed-dialog-backdrop" x-show="publishDialog" x-cloak x-transition.opacity
        @click.self="publishDialog = false" @keydown.escape.window="publishDialog = false">
        <div class="ed-dialog" role="dialog" aria-modal="true" aria-labelledby="publish-title">
            <h2 id="publish-title" class="ed-dialog__title">Antes de publicar</h2>
            <p class="ed-dialog__hint" x-show="examplesTotal > 0" title="Si no las cambias, tus invitados las verán tal cual.">
                <x-phosphor-info-light aria-hidden="true" />
                Quedan cosas de ejemplo: tus invitados las verían tal cual.
            </p>
            <ul class="ed-dialog__list">
                <template x-for="item in examplesLeft" :key="'ej-' + item.tab.id">
                    <li>
                        <button type="button" class="ed-dialog__row" @click="fixPart(item.tab.id)">
                            <span class="min-w-0 flex-1">
                                <strong x-text="item.tab.label"></strong>
                                <small x-text="item.count === 1 ? '1 cosa de ejemplo' : item.count + ' cosas de ejemplo'"></small>
                            </span>
                            <span class="sr-only">Cambiar</span>
                            <x-phosphor-caret-right-light aria-hidden="true" />
                        </button>
                    </li>
                </template>
                <template x-for="item in pendingIssues" :key="'pend-' + item.tabId + item.issue">
                    <li>
                        <button type="button" class="ed-dialog__row" @click="fixPart(item.tabId)">
                            <span class="min-w-0 flex-1">
                                <strong x-text="item.label"></strong>
                                <small x-text="item.issue"></small>
                            </span>
                            <span class="sr-only">Completar</span>
                            <x-phosphor-caret-right-light aria-hidden="true" />
                        </button>
                    </li>
                </template>
            </ul>
            <div class="ed-dialog__actions">
                <button type="button" class="ed-dialog__back" @click="publishDialog = false">
                    <x-phosphor-arrow-left-light aria-hidden="true" />
                    Seguir editando
                </button>
                <button type="button" class="site-btn" @click="publish()">Publicar igual</button>
            </div>
        </div>
    </div>

    {{--
        «Hazlo tú»: de qué depende el nivel de la invitación (de 0 a 3 puntos), con atajo para apagar cada parte que lo sube y
        el precio de hoy. Sin párrafos de explicación: el nivel sale de las partes que quedan encendidas.
    --}}
    <div class="ed-dialog-backdrop" x-show="tierDialog" x-cloak x-transition.opacity
        @click.self="tierDialog = false" @keydown.escape.window="tierDialog = false">
        <div class="ed-dialog" role="dialog" aria-modal="true" aria-labelledby="tier-title">
            <div class="ed-dialog__head">
                <span class="ed-dots" :data-level="diyTierIndex(diyRequiredTier)" aria-hidden="true"><i></i><i></i><i></i></span>
                <h2 id="tier-title" class="ed-dialog__title">Tu invitación hoy: <span x-text="diyTierName(diyRequiredTier)"></span></h2>
            </div>
            <p class="ed-dialog__hint" title="Mientras la armas, todo está abierto para que la pruebes. El nivel sale de las partes que dejes encendidas.">
                <x-phosphor-info-light aria-hidden="true" />
                El nivel sale de las partes encendidas. La gratuita trae portada, cuenta regresiva y ubicación.
            </p>
            {{-- El precio entero solo antes de pagar un nivel; subir de nivel cobra la diferencia (página de publicar) --}}
            <p class="ed-dialog__price" x-show="config.diyTiers?.prices?.[diyRequiredTier] && [null, 'gratis'].includes(config.diyTiers?.paid ?? null)" x-cloak>
                <strong x-text="config.diyTiers?.prices?.[diyRequiredTier]"></strong>
                <span x-text="(config.diyTiers?.paid ? 'subirla a ' + diyTierName(diyRequiredTier) : 'publicarla así') + ', un pago único'"></span>
            </p>
            <p class="ed-dialog__hint" x-show="config.diyTiers?.paid" x-cloak>
                <x-phosphor-seal-check-light aria-hidden="true" />
                Ya está publicada con el nivel <strong x-text="diyTierName(config.diyTiers?.paid)"></strong>.
            </p>
            <ul class="ed-dialog__list" x-show="diyPaidParts.length">
                <template x-for="part in diyPaidParts" :key="'nivel-' + part.id">
                    <li>
                        <div class="ed-dialog__row">
                            <span class="min-w-0 flex-1">
                                <strong x-text="part.label"></strong>
                                <small x-text="'Desde ' + part.tier"></small>
                            </span>
                            <button type="button" class="ed-dialog__off" @click="togglePart(flatTabs.find(tab => tab.id === part.id))" :aria-label="'Apagar ' + part.label" title="Apagar">
                                <x-phosphor-eye-slash-light aria-hidden="true" />
                                <span>Apagar</span>
                            </button>
                        </div>
                    </li>
                </template>
            </ul>
            <div class="ed-dialog__actions">
                <button type="button" class="site-btn" @click="tierDialog = false">Listo</button>
            </div>
        </div>
    </div>

    {{--
        Recortador: la foto se encuadra en el marco real del espacio donde va (proporción y forma de la plantilla,
        App\Support\ImageFrames). Lo que queda fuera se ve atenuado alrededor, para saber qué se pierde. Arrastrar mueve,
        la rueda o el pellizco acercan, las flechas mueven de a poco. Solo íconos: ✕ cancela, ✓ usa el encuadre; abajo,
        centrar y el acercamiento. En el celular ocupa toda la pantalla.
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

        <div class="admin-crop-panel" role="dialog" aria-modal="true" aria-labelledby="cropper-title">
            <div class="admin-crop-bar">
                <button type="button" @click="closeImageCropper()" class="admin-crop-btn" aria-label="Cancelar" title="Cancelar">
                    <x-phosphor-x-light aria-hidden="true" />
                </button>
                <h3 id="cropper-title" class="admin-crop-title">
                    <span class="sr-only">Encuadra la foto: </span>
                    <span x-text="cropperFrame?.label ?? 'Foto'"></span>
                </h3>
                <button type="button" @click="applyImageCrop()" class="admin-crop-btn admin-crop-btn--ok" aria-label="Usar encuadre" title="Usar encuadre">
                    <x-phosphor-check-light aria-hidden="true" />
                </button>
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

            <div class="admin-crop-foot">
                {{-- Si alcanza la calidad (un punto de color y pocas palabras); a cuántos píxeles se guarda, en el título --}}
                <p class="admin-crop-quality" :class="'is-' + (cropperQuality()?.level ?? 'good')" x-show="cropperQuality()" x-text="cropperQuality()?.text"
                    :title="cropperOutputSize() ? `Se guarda en ${cropperOutputSize().width} × ${cropperOutputSize().height} px` : ''"></p>
                <div class="admin-crop-zoom">
                    <button type="button" @click="cropperScale = cropperMinScale; cropperOffsetX = 0; cropperOffsetY = 0" class="admin-crop-btn" aria-label="Centrar" title="Centrar">
                        <x-phosphor-arrow-counter-clockwise-light aria-hidden="true" />
                    </button>
                    <button type="button" @click="cropperScale = Math.max(cropperMinScale, cropperScale - 0.1)" class="admin-crop-btn" aria-label="Alejar" title="Alejar">
                        <x-phosphor-magnifying-glass-minus-light aria-hidden="true" />
                    </button>
                    <input type="range" :min="cropperMinScale" :max="cropperMaxScale" step="0.01" x-model.number="cropperScale" class="adm-range flex-1" aria-label="Acercar o alejar">
                    <button type="button" @click="cropperScale = Math.min(cropperMaxScale, cropperScale + 0.1)" class="admin-crop-btn" aria-label="Acercar" title="Acercar">
                        <x-phosphor-magnifying-glass-plus-light aria-hidden="true" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
