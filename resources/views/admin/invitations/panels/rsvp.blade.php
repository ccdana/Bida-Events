<div x-show="activeTab === 'rsvp'" x-cloak class="space-y-4">
    <div class="admin-card space-y-4">
        <h2 class="font-serif text-lg text-stone-900">Confirmación (RSVP)</h2>
        <input type="text" x-model="modules.rsvp.titulo_confirmacion" class="admin-input" placeholder="Título">
        <textarea x-model="modules.rsvp.mensaje_personalizado" rows="2" class="admin-input" placeholder="Mensaje personalizado"></textarea>
        <input type="text" x-model="modules.rsvp.texto_confirmado" class="admin-input" placeholder="Texto al confirmar">
        <input type="text" x-model="modules.rsvp.texto_declinado" class="admin-input" placeholder="Texto al declinar">
    </div>
    <div class="admin-card space-y-4">
        <h2 class="font-serif text-lg text-stone-900">Post-evento</h2>
        <input type="text" x-model="modules.post_evento.titulo" class="admin-input" placeholder="Título galería fotógrafo">
        <textarea x-model="modules.post_evento.descripcion" rows="2" class="admin-input" placeholder="Descripción"></textarea>
        <input type="url" x-model="modules.post_evento.enlace_externo" class="admin-input" placeholder="Enlace externo galería">
    </div>
</div>
