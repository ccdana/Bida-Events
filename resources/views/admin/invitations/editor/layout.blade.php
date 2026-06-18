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
        class="admin-crop-backdrop backdrop-blur-sm"
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
        
        <div class="admin-crop-panel flex flex-col !max-w-lg relative"
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="opacity-0 translate-y-8 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-8 scale-95">
            
            <button type="button" @click="closeImageCropper()" class="absolute top-4 right-4 p-1.5 text-stone-400 hover:text-stone-600 hover:bg-stone-100 rounded-full transition-colors z-10">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>

            <div class="mb-5 pr-8">
                <h3 class="text-lg font-bold text-stone-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-stone-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
                    Ajustar imagen
                </h3>
                <p class="text-xs text-stone-500 mt-1">
                    Arrastra la imagen para posicionarla. Usa el deslizador o la rueda del ratón para hacer zoom.
                </p>
            </div>

            <div x-ref="cropperFrame" 
                class="admin-crop-frame bg-stone-900 rounded-xl shadow-inner border border-stone-200/50 cursor-grab active:cursor-grabbing overflow-hidden" 
                :style="`aspect-ratio:${cropperAspect}`"
                @mousedown.prevent="startCropDrag($event)"
                @touchstart.prevent="startCropDrag($event)"
                @wheel.prevent="onCropWheel($event)">
                <img :src="cropperBlobUrl"
                    alt=""
                    class="admin-crop-image pointer-events-none max-w-none max-h-none"
                    :style="cropperImageStyle()"
                    @load="onCropperImageLoad($event)">
                
                <div class="absolute inset-0 pointer-events-none grid grid-cols-3 grid-rows-3 opacity-30 mix-blend-overlay">
                    <div class="border-r border-b border-white"></div>
                    <div class="border-r border-b border-white"></div>
                    <div class="border-b border-white"></div>
                    <div class="border-r border-b border-white"></div>
                    <div class="border-r border-b border-white"></div>
                    <div class="border-b border-white"></div>
                    <div class="border-r border-white"></div>
                    <div class="border-r border-white"></div>
                    <div></div>
                </div>
            </div>

            <div class="mt-6 bg-stone-50 rounded-xl p-4 border border-stone-100 flex flex-col gap-3">
                <div class="flex items-center justify-between text-xs font-medium text-stone-600 px-1">
                    <span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path></svg> Zoom</span>
                    <span x-text="Math.round(cropperScale * 100) + '%'"></span>
                </div>
                
                <div class="flex items-center gap-3">
                    <button type="button" @click="cropperScale = Math.max(cropperMinScale, cropperScale - 0.1)" class="p-1 text-stone-400 hover:text-stone-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                    </button>
                    
                    <div class="relative flex-1 flex items-center">
                        <input type="range" min="1" max="3" step="0.01" x-model.number="cropperScale" 
                            class="w-full h-1.5 bg-stone-200 rounded-lg appearance-none cursor-pointer accent-stone-800 outline-none focus:ring-2 focus:ring-stone-400/50">
                    </div>
                    
                    <button type="button" @click="cropperScale = Math.min(cropperMaxScale, cropperScale + 0.1)" class="p-1 text-stone-400 hover:text-stone-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    </button>
                </div>
            </div>

            <div class="mt-6 flex flex-col-reverse sm:flex-row justify-between items-center gap-3">
                <button type="button"
                    @click="cropperScale = 1; cropperOffsetX = 0; cropperOffsetY = 0;"
                    class="w-full sm:w-auto px-3 py-2 rounded-lg text-xs font-medium text-stone-500 hover:text-stone-800 hover:bg-stone-100 transition-all flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Restablecer
                </button>
                
                <div class="flex gap-2 w-full sm:w-auto">
                    <button type="button"
                        @click="closeImageCropper()"
                        class="flex-1 sm:flex-none px-4 py-2 rounded-lg border border-stone-200 text-sm font-medium text-stone-600 hover:bg-stone-50 hover:border-stone-300 transition-all text-center">
                        Cancelar
                    </button>
                    <button type="button"
                        @click="applyImageCrop()"
                        class="flex-1 sm:flex-none px-4 py-2 rounded-lg bg-stone-900 text-sm font-semibold text-white shadow-md hover:bg-stone-800 hover:shadow-lg transition-all flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Aplicar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
