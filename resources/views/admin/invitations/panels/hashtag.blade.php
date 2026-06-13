<div x-show="activeTab === 'hashtag'" x-cloak class="space-y-4">
    <section class="admin-card space-y-4">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="admin-eyebrow">Hashtag</p>
                <h2 class="font-serif text-xl text-stone-950">Etiqueta oficial</h2>
                <p class="mt-1 text-sm text-stone-500">Comparte la etiqueta del evento sin mezclarla con encuestas o playlist.</p>
            </div>
            <label class="admin-toggle-row shrink-0">
                <input type="checkbox" x-model="modules.config.modulos.hashtag" class="rounded border-stone-300 text-amber-600 focus:ring-amber-500">
                <span class="text-stone-700">Activo</span>
            </label>
        </div>
        <input type="text" x-model="modules.hashtag.hashtag" class="admin-input" placeholder="#MiEvento2026">
        <select x-model="modules.hashtag.plataforma" class="admin-input">
            <option value="instagram">Instagram</option>
            <option value="tiktok">TikTok</option>
        </select>
        <input type="text" x-model="modules.hashtag.texto_boton" class="admin-input" placeholder="Texto del botón">
    </section>
</div>
