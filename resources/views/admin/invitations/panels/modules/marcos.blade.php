{{-- Panel de App\Modules\Card\FramesModule: fotos con marco y un pie escrito a mano --}}
<div x-show="activeTab === 'marcos'" x-cloak class="space-y-2">
    @include('admin.partials.panel-intro', [
        'eyebrow' => 'Fotos con marco',
        'title' => 'Enmarcados para siempre',
        'description' => 'dos fotos por hoja, cada una con un marco distinto: dorado antiguo, estampilla, óvalo con flores amarillas, instantánea y boleto.',
        'tip' => 'Escribe un pie corto para cada foto («Nuestro primer viaje»): aparece al lado, escrito a mano.',
        'moduleKey' => 'marcos',
        'countExpr' => '`${(modules.marcos.fotos || []).length} fotos`',
    ])

    <section class="admin-card p-3 space-y-2">
        <label class="admin-eyebrow mb-1 block" for="marcos-titulo">Título de la hoja</label>
        <input id="marcos-titulo" type="text" x-model="modules.marcos.titulo" @input="schedulePreview()" class="admin-input" maxlength="255" placeholder="Ej. Enmarcados para siempre">
    </section>

    @include('admin.invitations.panels.modules.partials.book-photos', [
        'code' => 'marcos',
        'max' => 12,
        'altPlaceholder' => 'Pie de foto, ej. Nuestro primer viaje',
        'altHelp' => 'El pie aparece junto a la foto y también lo leen los lectores de pantalla.',
    ])
</div>
