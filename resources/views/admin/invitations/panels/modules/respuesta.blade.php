{{-- Panel de App\Modules\Card\ReplyModule: textos del formulario de respuesta --}}
<div x-show="activeTab === 'respuesta'" x-cloak class="space-y-2">
    @include('admin.partials.panel-intro', [
        'eyebrow' => 'Respuesta',
        'title' => 'Que te pueda responder',
        'description' => 'un campo para escribir unas palabras de vuelta. La respuesta no se publica: llega al panel del cliente y solo él la lee.',
        'tip' => 'En la vista previa no se envían respuestas: pruébalo desde el enlace público.',
        'moduleKey' => 'respuesta',
    ])

    <section class="admin-card p-3 space-y-3">
        <div>
            <label class="admin-label" for="respuesta-titulo">Título</label>
            <input id="respuesta-titulo" type="text" x-model="modules.respuesta.titulo" @input="schedulePreview()" class="admin-input" maxlength="255" :placeholder="'Ej. Respóndele a ' + (modules.dedicatoria?.de || 'Luis')">
        </div>
        <div>
            <label class="admin-label" for="respuesta-descripcion">Texto de ayuda</label>
            <textarea id="respuesta-descripcion" x-model="modules.respuesta.descripcion" @input="schedulePreview()" rows="2" maxlength="1000" class="admin-input" placeholder="Ej. Escríbele unas palabras: solo él las va a leer."></textarea>
        </div>
        <div>
            <label class="admin-label" for="respuesta-placeholder">Ejemplo dentro del campo</label>
            <input id="respuesta-placeholder" type="text" x-model="modules.respuesta.placeholder" @input="schedulePreview()" class="admin-input" maxlength="255" placeholder="Ej. Escribe tu respuesta…">
        </div>
    </section>
</div>
