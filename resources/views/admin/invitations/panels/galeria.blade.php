<div x-show="activeTab === 'galeria'" x-cloak class="admin-card space-y-4">
    <h2 class="font-serif text-lg text-stone-900">Galería de fotos</h2>
    <div>
        <label class="admin-label">Título de sección</label>
        <input type="text" x-model="modules.galeria.titulo" class="admin-input">
    </div>
    <div>
        <label class="admin-label">URLs de fotos (una por línea)</label>
        <textarea x-model="galeriaText" rows="8" class="admin-input font-mono text-xs" placeholder="https://res.cloudinary.com/..."></textarea>
        <p class="text-[10px] text-stone-400 mt-1"><span x-text="(modules.galeria.fotos || []).length"></span> fotos cargadas</p>
    </div>
</div>
