{{-- Panel de App\Modules\Card\CollageModule: fotos que se acomodan en formas con flores amarillas --}}
<div x-show="activeTab === 'collage'" x-cloak class="space-y-2">
    @include('admin.partials.panel-intro', [
        'eyebrow' => 'Collage',
        'title' => 'Fotos en formas',
        'description' => 'las fotos se reparten en hojas con diseños que se turnan: un corazón, un girasol de fotos, tiras de fotomatón, instantáneas encimadas y círculos con margaritas.',
        'tip' => 'Entre 8 y 16 fotos llenan varias hojas. Las primeras 3 van en el corazón; la primera de cada grupo de 5 va al centro del girasol.',
        'moduleKey' => 'collage',
        'countExpr' => '`${(modules.collage.fotos || []).length} fotos`',
    ])

    <section class="admin-card p-3 space-y-2">
        <label class="admin-eyebrow mb-1 block" for="collage-titulo">Título de la primera hoja</label>
        <input id="collage-titulo" type="text" x-model="modules.collage.titulo" @input="schedulePreview()" class="admin-input" maxlength="255" placeholder="Ej. Nuestro collage">
    </section>

    @include('admin.invitations.panels.modules.partials.book-photos', [
        'code' => 'collage',
        'max' => 30,
        'altPlaceholder' => 'Describe la foto (opcional)',
        'altHelp' => 'La descripción la leen los lectores de pantalla. El orden de la cuadrícula es el orden del collage.',
    ])
</div>
