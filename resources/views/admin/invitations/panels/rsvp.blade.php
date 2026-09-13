<div x-show="activeTab === 'rsvp'" x-cloak class="space-y-2">
    @include('admin.partials.panel-intro', [
        'eyebrow' => 'RSVP',
        'title' => 'Confirmación de asistencia',
        'description' => 'solo aparece al abrir el enlace personal de cada invitado. Responde en pasos (¿asistirás?, ¿cuántas personas?) y, al confirmar, recibe un pase con código QR.',
        'tip' => 'La vista previa no muestra este formulario porque no hay un invitado seleccionado. Revísalo abriendo el enlace de un invitado desde «Invitados».',
        'moduleKey' => 'rsvp',
    ])

    <section class="admin-card p-3 space-y-3">
        <div class="grid gap-2">
            <div>
                <label class="admin-label">Título del formulario</label>
                <input type="text" x-model="modules.rsvp.titulo_confirmacion" @input="schedulePreview()" class="admin-input" placeholder="Ej. ¿Nos acompañas?">
            </div>
            <div>
                <label class="admin-label">Mensaje debajo del título</label>
                <textarea x-model="modules.rsvp.mensaje_personalizado" @input="schedulePreview()" rows="2" class="admin-input" placeholder="Ej. Por favor confirma antes del 10 de noviembre."></textarea>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="admin-label">Mensaje en el pase</label>
                    <input type="text" x-model="modules.rsvp.texto_confirmado" @input="schedulePreview()" class="admin-input" placeholder="Ej. Presenta este pase en la entrada">
                    <p class="mt-1 text-[11px] text-stone-400">Se ve cuando el invitado confirma.</p>
                </div>
                <div>
                    <label class="admin-label">Título si no asistirá</label>
                    <input type="text" x-model="modules.rsvp.texto_declinado" @input="schedulePreview()" class="admin-input" placeholder="Ej. Gracias por avisarnos">
                    <p class="mt-1 text-[11px] text-stone-400">Se ve cuando el invitado responde que no irá.</p>
                </div>
            </div>
        </div>
    </section>
</div>
