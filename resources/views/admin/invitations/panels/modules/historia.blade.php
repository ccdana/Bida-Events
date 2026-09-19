{{--
    Panel de App\Modules\Card\StoryModule: lo que se cuenta en cada acto de «Nuestra historia».
    Nombres y primera foto van en «Banner principal»; la fecha en «Juntos desde»; fotos actuales en
    «Galería»; la canción en «Música». Todo es opcional: un campo vacío muestra un texto de respaldo.
--}}
@php($storyQuotes = \App\Modules\Card\StoryModule::QUOTES)

<div x-show="activeTab === 'historia'" x-cloak class="space-y-2">
    @include('admin.partials.panel-intro', [
        'eyebrow' => 'Nuestra historia',
        'title' => 'Lo que cuenta cada acto',
        'description' => 'cuatro actos: la luna reflejada en el agua, la marea de recuerdos, la luna de frente con su anécdota y el cielo estrellado del final.',
        'tip' => 'Nada es obligatorio. Si dejas un campo vacío, el acto usa un texto propio para no perder su sentido.',
        'moduleKey' => 'historia',
    ])

    <section class="admin-card p-3 space-y-3">
        <p class="text-sm font-semibold text-stone-900">Acto I · El reflejo</p>
        <div>
            <label class="admin-label" for="historia-primeras">Primeras impresiones</label>
            <textarea id="historia-primeras" x-model="modules.historia.primeras_impresiones" @input="schedulePreview()" rows="4" maxlength="1500" class="admin-input" placeholder="Lo que pensaron el uno del otro al principio, antes de saber lo que venía."></textarea>
        </div>
        <p class="text-[11px] text-site-muted">La foto de este acto es la del «Banner principal» y la fecha es la de «Juntos desde».</p>
    </section>

    <section class="admin-card p-3 space-y-3">
        <div class="flex items-center justify-between gap-2">
            <p class="text-sm font-semibold text-stone-900">Acto II · La marea</p>
            <span class="text-[11px] text-site-muted" x-text="`${modules.historia.momentos.length} de {{ \App\Modules\Card\StoryModule::MAX_MOMENTS }} momentos`"></span>
        </div>

        <template x-for="(momento, i) in modules.historia.momentos" :key="i">
            <div class="rounded-xl border border-stone-200 p-3 space-y-2">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold text-stone-700" x-text="`Momento ${i + 1}`"></p>
                    <button type="button" class="text-xs text-red-600 hover:text-red-800"
                        @click="clearMediaUrl(momento.foto); modules.historia.momentos.splice(i, 1); schedulePreview()">Quitar</button>
                </div>
                <div class="grid gap-2 sm:grid-cols-[8rem_1fr]">
                    <div>
                        <label class="admin-label" :for="`historia-cuando-${i}`">Cuándo</label>
                        <input :id="`historia-cuando-${i}`" type="text" x-model="momento.cuando" @input="schedulePreview()" class="admin-input" maxlength="100" placeholder="Ej. Mayo 2019">
                    </div>
                    <div>
                        <label class="admin-label" :for="`historia-titulo-${i}`">Qué pasó</label>
                        <input :id="`historia-titulo-${i}`" type="text" x-model="momento.titulo" @input="schedulePreview()" class="admin-input" maxlength="255" placeholder="Ej. Nuestro primer viaje">
                    </div>
                </div>
                <div>
                    <label class="admin-label" :for="`historia-descripcion-${i}`">Cómo lo recuerdan</label>
                    <textarea :id="`historia-descripcion-${i}`" x-model="momento.descripcion" @input="schedulePreview()" rows="2" maxlength="1000" class="admin-input" placeholder="Una o dos frases."></textarea>
                </div>
                @include('admin.partials.cloudinary-upload', [
                    'label' => 'Foto del momento (opcional)',
                    'type' => 'image',
                    'context' => 'story',
                    'accept' => 'image/jpeg,image/png,image/webp',
                    'previewExpr' => 'momento.foto',
                ])
            </div>
        </template>

        <button type="button"
            class="flex w-full items-center justify-center gap-2 rounded-lg border-2 border-dashed border-stone-200 bg-stone-50 py-2.5 text-xs font-semibold text-stone-600 hover:border-amber-300 hover:bg-amber-50 hover:text-amber-700 transition"
            x-show="modules.historia.momentos.length < {{ \App\Modules\Card\StoryModule::MAX_MOMENTS }}"
            @click="modules.historia.momentos.push({ cuando: '', titulo: '', descripcion: '', foto: null }); schedulePreview()">
            <x-phosphor-plus class="w-4 h-4" aria-hidden="true" />
            Agregar momento
        </button>

        <div>
            <label class="admin-label" for="historia-cita">Cita entre los momentos</label>
            <select id="historia-cita" x-model="modules.historia.cita" @change="schedulePreview()" class="admin-input">
                <option value="">La de siempre ({{ $storyQuotes[\App\Modules\Card\StoryModule::DEFAULT_QUOTE]['author'] }})</option>
                @foreach($storyQuotes as $key => $quote)
                    <option value="{{ $key }}">{{ $quote['author'] }}: «{{ \Illuminate\Support\Str::limit($quote['text'], 48) }}»</option>
                @endforeach
            </select>
        </div>
    </section>

    <section class="admin-card p-3 space-y-3">
        <p class="text-sm font-semibold text-stone-900">Acto III · De frente</p>
        <div>
            <label class="admin-label" for="historia-anecdota-titulo">Nombre de la anécdota</label>
            <input id="historia-anecdota-titulo" type="text" x-model="modules.historia.anecdota_titulo" @input="schedulePreview()" class="admin-input" maxlength="255" placeholder="Ej. La noche del paraguas">
        </div>
        <div>
            <label class="admin-label" for="historia-anecdota">La anécdota</label>
            <textarea id="historia-anecdota" x-model="modules.historia.anecdota" @input="schedulePreview()" rows="6" maxlength="3000" class="admin-input" placeholder="El momento pequeño que lo significó todo. Cuéntalo como se lo contarían a un amigo."></textarea>
            <p class="mt-1 text-[11px] text-site-muted"><span x-text="3000 - (modules.historia.anecdota || '').length"></span> caracteres disponibles</p>
        </div>
        @include('admin.partials.cloudinary-upload', [
            'label' => 'Foto de ese momento (opcional)',
            'type' => 'image',
            'context' => 'story',
            'accept' => 'image/jpeg,image/png,image/webp',
            'previewExpr' => 'modules.historia.anecdota_foto',
        ])
        <p class="text-[11px] text-site-muted">Sin foto, este acto muestra la del banner, ahora nítida: es la misma imagen del reflejo, vista de frente.</p>
    </section>

    <section class="admin-card p-3 space-y-3">
        <p class="text-sm font-semibold text-stone-900">Acto IV · La constelación</p>
        <div>
            <label class="admin-label" for="historia-reflexion">Cómo cambió su vida</label>
            <textarea id="historia-reflexion" x-model="modules.historia.reflexion" @input="schedulePreview()" rows="4" maxlength="2000" class="admin-input" placeholder="Cómo ven la vida ahora que están juntos."></textarea>
        </div>
        <div>
            <label class="admin-label" for="historia-promesa">Promesa</label>
            <input id="historia-promesa" type="text" x-model="modules.historia.promesa" @input="schedulePreview()" class="admin-input" maxlength="1000" placeholder="La última línea de la historia.">
        </div>
    </section>
</div>
