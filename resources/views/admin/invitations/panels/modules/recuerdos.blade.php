{{-- Panel de App\Modules\Card\MemoriesModule: recuerdos especiales con foto, fecha y una nota --}}
<div x-show="activeTab === 'recuerdos'" x-cloak class="space-y-2">
    @include('admin.partials.panel-intro', [
        'eyebrow' => 'Recuerdos especiales',
        'title' => 'Fotos con historia',
        'description' => 'dos recuerdos por hoja, cada uno como una instantánea pegada con cinta, con su fecha y una nota escrita a mano al lado.',
        'tip' => 'Una nota corta se lee mejor: una o dos frases por recuerdo.',
        'moduleKey' => 'recuerdos',
        'countExpr' => '`${(modules.recuerdos.recuerdos || []).length} recuerdos`',
    ])

    <section class="admin-card p-3 space-y-2">
        <label class="admin-eyebrow mb-1 block" for="recuerdos-titulo">Título</label>
        <input id="recuerdos-titulo" type="text" x-model="modules.recuerdos.titulo" @input="schedulePreview()" class="admin-input" maxlength="255" placeholder="Ej. Recuerdos especiales">
    </section>

    @include('admin.invitations.panels.modules.partials.book-entries', [
        'code' => 'recuerdos',
        'key' => 'recuerdos',
        'max' => 12,
        'noun' => 'recuerdo',
        'textLimit' => \App\Modules\Card\MemoriesModule::BODY_LIMIT,
        'textRows' => 3,
        'textLabel' => 'Nota',
        'textPlaceholder' => 'Ej. El día que nos perdimos y encontramos el mejor café.',
    ])
</div>
