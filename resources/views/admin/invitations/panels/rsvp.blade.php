<div x-show="activeTab === 'rsvp'" x-cloak class="space-y-4">
    <section class="admin-card space-y-4">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="admin-eyebrow">RSVP</p>
                <h2 class="font-serif text-xl text-stone-950">Confirmación de asistencia</h2>
                <p class="mt-1 text-sm text-stone-500">Sección dedicada solo a la respuesta de invitados.</p>
            </div>
            <label class="admin-toggle-row shrink-0">
                <input type="checkbox" x-model="modules.config.modulos.rsvp" class="rounded border-stone-300 text-amber-600 focus:ring-amber-500">
                <span class="text-stone-700">Activo</span>
            </label>
        </div>
        <input type="text" x-model="modules.rsvp.titulo_confirmacion" class="admin-input" placeholder="Título">
        <textarea x-model="modules.rsvp.mensaje_personalizado" rows="2" class="admin-input" placeholder="Mensaje personalizado"></textarea>
        <input type="text" x-model="modules.rsvp.texto_confirmado" class="admin-input" placeholder="Texto al confirmar">
        <input type="text" x-model="modules.rsvp.texto_declinado" class="admin-input" placeholder="Texto al declinar">
    </section>
</div>
