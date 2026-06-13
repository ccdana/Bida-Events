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
</div>
