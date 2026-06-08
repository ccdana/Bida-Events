<div x-show="activeTab === 'regalos'" x-cloak class="space-y-4">
    <section class="admin-card space-y-4">
        <div>
            <p class="admin-eyebrow">Regalos</p>
            <h2 class="font-serif text-xl text-stone-950">Detalles y opciones</h2>
            <p class="mt-1 text-sm text-stone-500">Puedes combinar tienda, sobres, banco y otras alternativas en una sola sección ordenada.</p>
        </div>
        <div>
            <label class="admin-label">Título sección</label>
            <input type="text" x-model="modules.regalos.titulo" class="admin-input">
        </div>
        <div>
            <label class="admin-label">URL tienda de regalos <span class="normal-case text-stone-400">(opcional, externo)</span></label>
            <input type="url" x-model="modules.regalos.tienda_url" class="admin-input" placeholder="Solo si tienes lista en tienda externa">
        </div>
        <div>
            <label class="admin-label">Texto botón tienda</label>
            <input type="text" x-model="modules.regalos.tienda_texto" class="admin-input">
        </div>
    </section>

    <section class="admin-card space-y-3">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="admin-label mb-1">Más opciones de regalo</p>
                <p class="text-xs text-stone-500">Agrega enlaces extra o tarjetas especiales para otras ideas de regalo.</p>
            </div>
            <button type="button" @click="addGiftOption()" class="admin-link-button">+ Opción</button>
        </div>
        <template x-for="(option, i) in modules.regalos.opciones" :key="'gift-'+i">
            <div class="rounded-2xl border border-stone-200 bg-stone-50 p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="admin-context-badge is-secondary">Opción <span x-text="i + 1"></span></span>
                    <button type="button" @click="removeGiftOption(i)" class="text-xs text-red-600 hover:text-red-700">Eliminar</button>
                </div>
                <input type="text" x-model="option.titulo" class="admin-input" placeholder="Título">
                <textarea x-model="option.descripcion" rows="2" class="admin-input" placeholder="Descripción"></textarea>
                <div class="grid gap-2 md:grid-cols-[1fr_1.4fr]">
                    <input type="text" x-model="option.icono" class="admin-input" placeholder="icono / tipo">
                    <input type="url" x-model="option.enlace" class="admin-input" placeholder="URL de referencia">
                </div>
            </div>
        </template>
    </section>

    <section class="admin-card space-y-4">
        <div class="pt-0 space-y-2">
            <p class="admin-label">Lluvia de sobres</p>
            <input type="text" x-model="modules.regalos.sobres.titulo" class="admin-input" placeholder="Título">
            <input type="text" x-model="modules.regalos.sobres.direccion" class="admin-input" placeholder="Dirección física para sobres">
        </div>
    </section>

    <section class="admin-card space-y-3">
        <p class="admin-label">Datos bancarios</p>
        <input type="text" x-model="modules.regalos.banco.banco" class="admin-input" placeholder="Banco">
        <input type="text" x-model="modules.regalos.banco.titular" class="admin-input" placeholder="Titular">
        <div class="grid grid-cols-1 gap-2 md:grid-cols-2">
            <input type="text" x-model="modules.regalos.banco.ci" class="admin-input" placeholder="CI">
            <input type="text" x-model="modules.regalos.banco.cuenta" class="admin-input" placeholder="Cuenta">
        </div>
        @include('admin.partials.cloudinary-upload', [
            'label' => 'QR de transferencia',
            'type' => 'image',
            'context' => 'qr-banco',
            'accept' => 'image/jpeg,image/png,image/webp',
            'previewExpr' => 'modules.regalos.banco.qr_url',
        ])
    </section>
</div>
