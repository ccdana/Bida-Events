<div x-show="activeTab === 'bienvenida'" x-cloak class="admin-card space-y-4">
    <h2 class="font-serif text-lg text-stone-900">Bienvenida / Hero</h2>
    <div>
        <label class="admin-label">Nombre quinceañera / protagonista</label>
        <input type="text" x-model="modules.bienvenida.nombre_quinceanera" class="admin-input">
    </div>
    <div>
        <label class="admin-label">Subtítulo</label>
        <input type="text" x-model="modules.bienvenida.subtitulo" class="admin-input">
    </div>
    <div>
        <label class="admin-label">Mensaje de bienvenida</label>
        <textarea x-model="modules.bienvenida.mensaje" rows="4" class="admin-input"></textarea>
    </div>
    <div>
        <label class="admin-label">Texto de fecha (visible)</label>
        <input type="text" x-model="modules.bienvenida.fecha_texto" class="admin-input">
    </div>
    <div>
        <label class="admin-label">Mensaje post-evento</label>
        <textarea x-model="modules.bienvenida.mensaje_post_evento" rows="2" class="admin-input"></textarea>
    </div>
    <div>
        <label class="admin-label">Imagen hero (URL Cloudinary)</label>
        <input type="url" x-model="modules.bienvenida.imagen_hero" class="admin-input" placeholder="https://res.cloudinary.com/...">
    </div>
</div>
