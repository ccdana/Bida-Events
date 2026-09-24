<div x-show="activeTab === 'rsvp'" x-cloak class="space-y-2">
    @include('admin.partials.panel-intro', [
        'eyebrow' => 'RSVP',
        'title' => 'Confirmación de asistencia',
        'description' => 'responde en pasos (¿asistirás?, ¿cuántas personas?). En Premium la respuesta queda guardada y recibe un pase con código QR (solo en su enlace personal); en Estándar se abre WhatsApp con su respuesta lista para el número de abajo.',
        'tip' => 'Con pase QR, la vista previa no muestra el formulario porque no hay un invitado seleccionado: revísalo con el enlace de un invitado desde «Invitados».',
        'moduleKey' => 'rsvp',
    ])

    {{-- Paquete Estándar: a quién le llegan las respuestas --}}
    <section class="admin-card p-3 space-y-2" x-show="rsvpMode === 'whatsapp'" x-cloak>
        <label for="rsvp-whatsapp" class="admin-label">WhatsApp que recibe las confirmaciones</label>
        <input id="rsvp-whatsapp" type="tel" inputmode="tel" x-model="modules.rsvp.whatsapp" @input="schedulePreview()" class="admin-input" placeholder="Ej. 59171234567">
        <p class="text-xs text-site-muted">Con código de país y sin espacios. Normalmente, el de quien organiza el evento.</p>
    </section>

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
