<div class="hidden min-w-0 flex-1 flex-col bg-site-surface lg:flex">
    <div class="flex shrink-0 items-center justify-between gap-4 border-b border-site-line px-5 py-3">
        <p class="flex items-center gap-2.5 text-sm font-medium" aria-live="polite">
            <span class="relative flex size-2">
                <span class="absolute inline-flex size-full animate-ping rounded-full bg-site-accent opacity-60" x-show="previewLoading"></span>
                <span class="relative inline-flex size-2 rounded-full bg-site-accent"></span>
            </span>
            <span x-text="previewLoading ? 'Actualizando vista previa' : 'Vista previa'">Vista previa</span>
        </p>
        <p class="text-xs text-site-muted">Así la verán tus invitados en el celular</p>
    </div>
    <div class="min-h-0 flex-1 p-6">
        <div class="adm-device mx-auto h-full max-w-[390px]">
            <iframe x-ref="previewFrame" src="about:blank" class="size-full rounded-[1.6rem] bg-white" title="Vista previa de la invitación"></iframe>
        </div>
    </div>
</div>
