<div x-show="activeTab === 'regalos'" x-cloak class="admin-card space-y-4">
    <h2 class="font-serif text-lg text-stone-900">Regalos y detalles</h2>
    <div>
        <label class="admin-label">Título sección</label>
        <input type="text" x-model="modules.regalos.titulo" class="admin-input">
    </div>
    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="admin-label">URL tienda de regalos</label>
            <input type="url" x-model="modules.regalos.tienda_url" class="admin-input">
        </div>
        <div>
            <label class="admin-label">Texto botón tienda</label>
            <input type="text" x-model="modules.regalos.tienda_texto" class="admin-input">
        </div>
    </div>
    <div class="pt-3 border-t border-stone-100 space-y-2">
        <p class="admin-label">Lluvia de sobres</p>
        <input type="text" x-model="modules.regalos.sobres.titulo" class="admin-input" placeholder="Título">
        <input type="text" x-model="modules.regalos.sobres.direccion" class="admin-input" placeholder="Dirección">
    </div>
    <div class="pt-3 border-t border-stone-100 space-y-2">
        <p class="admin-label">Datos bancarios</p>
        <input type="text" x-model="modules.regalos.banco.banco" class="admin-input" placeholder="Banco">
        <input type="text" x-model="modules.regalos.banco.titular" class="admin-input" placeholder="Titular">
        <div class="grid grid-cols-2 gap-2">
            <input type="text" x-model="modules.regalos.banco.ci" class="admin-input" placeholder="CI">
            <input type="text" x-model="modules.regalos.banco.cuenta" class="admin-input" placeholder="Cuenta">
        </div>
        <input type="url" x-model="modules.regalos.banco.qr_url" class="admin-input" placeholder="URL QR transferencia">
    </div>
</div>
