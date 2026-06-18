<div x-show="activeTab === 'rsvp'" x-cloak class="space-y-2">
    <section class="admin-card p-3 space-y-3">
        <div class="flex items-center justify-between gap-2">
            <div class="min-w-0">
                <p class="admin-eyebrow mb-0.5">RSVP</p>
                <p class="text-xs text-stone-600 truncate">Confirmación de asistencia de invitados</p>
            </div>
            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md border text-xs font-semibold shrink-0"
                :class="modules.config.modulos.rsvp
                    ? 'text-green-700 bg-green-50 border-green-200'
                    : 'text-stone-500 bg-stone-50 border-stone-200'">
                <span class="inline-block w-1.5 h-1.5 rounded-full"
                    :class="modules.config.modulos.rsvp ? 'bg-green-500' : 'bg-stone-400'"></span>
                <span x-text="modules.config.modulos.rsvp ? 'Activo' : 'Inactivo'"></span>
            </span>
        </div>

        <div class="grid gap-2">
            <div>
                <label class="admin-label">Título de confirmación</label>
                <input type="text" x-model="modules.rsvp.titulo_confirmacion" @input="schedulePreview()" class="admin-input" placeholder="Ej. Confirmación de asistencia">
            </div>
            <div>
                <label class="admin-label">Mensaje personalizado</label>
                <textarea x-model="modules.rsvp.mensaje_personalizado" @input="schedulePreview()" rows="2" class="admin-input" placeholder="Ej. Por favor confirma tu asistencia antes del 10 de Julio."></textarea>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="admin-label">Texto al confirmar</label>
                    <input type="text" x-model="modules.rsvp.texto_confirmado" @input="schedulePreview()" class="admin-input" placeholder="Ej. ¡Sí, asistiré!">
                </div>
                <div>
                    <label class="admin-label">Texto al declinar</label>
                    <input type="text" x-model="modules.rsvp.texto_declinado" @input="schedulePreview()" class="admin-input" placeholder="Ej. No podré asistir">
                </div>
            </div>
        </div>
    </section>
</div>
