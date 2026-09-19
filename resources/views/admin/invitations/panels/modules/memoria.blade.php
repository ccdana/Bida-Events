{{-- Panel de App\Modules\Card\MemoryGameModule: fotos del juego de memoria y mensaje al ganar --}}
<div x-show="activeTab === 'memoria'" x-cloak class="space-y-2">
    @include('admin.partials.panel-intro', [
        'eyebrow' => 'Juego de memoria',
        'title' => 'Encuentra los pares',
        'description' => 'cada foto aparece dos veces boca abajo; quien recibe el libro las da vuelta hasta encontrar todos los pares. Al terminar caen pétalos y ve tu mensaje.',
        'tip' => 'Usa entre 3 y 8 fotos bien distintas entre sí: con 6 fotos el juego tiene 12 cartas.',
        'moduleKey' => 'memoria',
        'countExpr' => '`${(modules.memoria.fotos || []).length} de 8 fotos`',
    ])

    <section class="admin-card p-3 space-y-3">
        <div>
            <label class="admin-label" for="memoria-titulo">Título</label>
            <input id="memoria-titulo" type="text" x-model="modules.memoria.titulo" @input="schedulePreview()" class="admin-input" maxlength="255" placeholder="Ej. Encuentra los pares">
        </div>
        <div>
            <label class="admin-label" for="memoria-mensaje">Mensaje al ganar</label>
            <textarea id="memoria-mensaje" x-model="modules.memoria.mensaje_final" @input="schedulePreview()" rows="3" maxlength="300" class="admin-input" placeholder="Ej. ¡Los encontraste todos! Así de bien nos complementamos."></textarea>
        </div>
        <p class="text-xs text-amber-700" x-show="(modules.memoria.fotos || []).length > 0 && (modules.memoria.fotos || []).length < 3" x-cloak>
            Faltan fotos: el juego necesita al menos 3.
        </p>
    </section>

    @include('admin.invitations.panels.modules.partials.book-photos', [
        'code' => 'memoria',
        'max' => 8,
        'altPlaceholder' => 'Describe la foto (opcional)',
        'altHelp' => 'La descripción la escucha quien usa lector de pantalla al dar vuelta la carta.',
    ])
</div>
