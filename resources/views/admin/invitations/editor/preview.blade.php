<div class="hidden lg:flex flex-1 flex-col bg-stone-200/60 min-w-0">
    <div class="shrink-0 flex items-center justify-between px-4 py-2.5 border-b border-stone-200/80 bg-white/80 backdrop-blur-sm">
        <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full" :class="previewLoading ? 'bg-amber-400 animate-pulse' : 'bg-emerald-400'"></span>
            <span class="admin-context-badge is-live">Vista previa</span>
        </div>
        <button type="button" @click="runPreviewUpdate()" class="admin-link-button">Actualizar</button>
    </div>
    <div class="flex-1 p-4 min-h-0">
        <div class="mx-auto h-full max-w-[390px] rounded-[2rem] border-[8px] border-stone-400 bg-stone-200 shadow-[0_24px_60px_rgba(28,25,23,0.16)] overflow-hidden">
            <div class="h-6 flex items-center justify-center bg-stone-300">
                <div class="w-16 h-1 rounded-full bg-stone-500/60"></div>
            </div>
            <iframe x-ref="previewFrame" src="about:blank"
                class="w-full bg-white" style="height: calc(100% - 1.5rem)" title="Vista previa"></iframe>
        </div>
    </div>
</div>
