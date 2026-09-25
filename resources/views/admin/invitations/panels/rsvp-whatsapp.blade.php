<div x-show="activeTab === 'rsvp_whatsapp'" x-cloak class="space-y-2">
    @include('admin.partials.panel-intro', [
        'eyebrow' => 'Confirmación',
        'title' => 'Confirmación por WhatsApp',
        'description' => 'responde en pasos (¿asistirás?, ¿cuántas personas?) y al final se abre WhatsApp con su respuesta lista para el número de abajo. La respuesta no queda guardada en el sistema.',
        'tip' => 'Es una de las dos formas de confirmar: si la enciendes, se apaga la confirmación con pase QR.',
        'moduleKey' => 'rsvp_whatsapp',
    ])

    <section class="admin-card p-3 space-y-2">
        <label for="rsvp-whatsapp" class="admin-label">WhatsApp que recibe las confirmaciones</label>
        <input id="rsvp-whatsapp" type="tel" inputmode="tel" x-model="modules.rsvp_whatsapp.whatsapp" @input="schedulePreview()" class="admin-input" placeholder="Ej. 59171234567">
        <p class="text-xs text-site-muted">Con código de país y sin espacios. Normalmente, el de quien organiza el evento.</p>
    </section>

    {{-- Título y mensaje: los mismos de la confirmación con pase (se comparten) --}}
    <section class="admin-card p-3 space-y-3">
        <div>
            <label class="admin-label" for="rsvp-wa-title">Título del formulario</label>
            <input id="rsvp-wa-title" type="text" x-model="modules.rsvp.titulo_confirmacion" @input="schedulePreview()" class="admin-input" placeholder="Ej. ¿Nos acompañas?">
        </div>
        <div>
            <label class="admin-label" for="rsvp-wa-message">Mensaje debajo del título</label>
            <textarea id="rsvp-wa-message" x-model="modules.rsvp.mensaje_personalizado" @input="schedulePreview()" rows="2" class="admin-input" placeholder="Ej. Por favor confirma antes del 10 de noviembre."></textarea>
        </div>
    </section>
</div>
