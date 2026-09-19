{{-- Panel de App\Modules\Card\DedicationModule: de quién, para quién, mensaje y firma --}}
<div x-show="activeTab === 'dedicatoria'" x-cloak class="space-y-2">
    @include('admin.partials.panel-intro', [
        'eyebrow' => 'Dedicatoria',
        'title' => 'Lo que dice la carta',
        'description' => 'una carta escrita a mano con su nombre al inicio, tu mensaje y tu firma al final. Los nombres también aparecen en la carta cerrada y en la vista previa de WhatsApp.',
        'tip' => 'Deja una línea en blanco para separar párrafos. Un mensaje corto y personal se lee mejor que uno largo.',
        'moduleKey' => 'dedicatoria',
    ])

    <section class="admin-card p-3 space-y-3">
        <div class="grid gap-3 sm:grid-cols-2">
            <div>
                <label class="admin-label" for="dedicatoria-para">Para</label>
                <input id="dedicatoria-para" type="text" x-model="modules.dedicatoria.para" @input="schedulePreview()" class="admin-input" maxlength="255" placeholder="Ej. Ana">
            </div>
            <div>
                <label class="admin-label" for="dedicatoria-de">De</label>
                <input id="dedicatoria-de" type="text" x-model="modules.dedicatoria.de" @input="schedulePreview()" class="admin-input" maxlength="255" placeholder="Ej. Luis">
            </div>
        </div>

        <div>
            <label class="admin-label" for="dedicatoria-mensaje">Mensaje</label>
            <textarea id="dedicatoria-mensaje" x-model="modules.dedicatoria.mensaje" @input="schedulePreview()" rows="7" maxlength="3000" class="admin-input" placeholder="Escribe aquí lo que quieres decirle."></textarea>
            <p class="mt-1 text-[11px] text-site-muted"><span x-text="3000 - (modules.dedicatoria.mensaje || '').length"></span> caracteres disponibles</p>
        </div>

        <div>
            <label class="admin-label" for="dedicatoria-firma">Firma</label>
            <input id="dedicatoria-firma" type="text" x-model="modules.dedicatoria.firma" @input="schedulePreview()" class="admin-input" maxlength="255" placeholder="Ej. Tu Luis (si la dejas vacía, firma con el nombre de «De»)">
        </div>
    </section>
</div>
