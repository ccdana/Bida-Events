{{-- Panel de App\Modules\Card\StoryModule: capítulos de su historia; un capítulo largo ocupa varias hojas --}}
<div x-show="activeTab === 'historia'" x-cloak class="space-y-2">
    @include('admin.partials.panel-intro', [
        'eyebrow' => 'Nuestra historia',
        'title' => 'Capítulo por capítulo',
        'description' => 'una hoja con el índice y cada capítulo con su fecha como sello, su título y una foto. Si el texto es largo, sigue solo en las hojas siguientes.',
        'tip' => 'Deja una línea en blanco entre párrafos. Por ejemplo: «Cómo nos conocimos», «Nuestra primera cita», «El viaje que no olvidamos».',
        'moduleKey' => 'historia',
        'countExpr' => '`${(modules.historia.capitulos || []).length} capítulos`',
    ])

    <section class="admin-card p-3 space-y-2">
        <label class="admin-eyebrow mb-1 block" for="historia-titulo">Título</label>
        <input id="historia-titulo" type="text" x-model="modules.historia.titulo" @input="schedulePreview()" class="admin-input" maxlength="255" placeholder="Ej. Nuestra historia">
    </section>

    @include('admin.invitations.panels.modules.partials.book-entries', [
        'code' => 'historia',
        'key' => 'capitulos',
        'max' => 12,
        'noun' => 'capítulo',
        'textLimit' => \App\Modules\Card\StoryModule::BODY_LIMIT,
        'textRows' => 8,
        'textLabel' => 'Lo que pasó',
        'textPlaceholder' => 'Cuenta este capítulo de su historia.',
    ])
</div>
