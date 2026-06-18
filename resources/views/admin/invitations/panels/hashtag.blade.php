<div x-show="activeTab === 'hashtag'" x-cloak class="space-y-2">
    <section class="admin-card p-3 space-y-3">
        <div class="flex items-center justify-between gap-2">
            <div class="min-w-0">
                <p class="admin-eyebrow mb-0.5">Hashtag</p>
                <p class="text-xs text-stone-600 truncate">Etiqueta oficial para redes sociales</p>
            </div>
            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md border text-xs font-semibold shrink-0"
                :class="modules.config.modulos.hashtag
                    ? 'text-green-700 bg-green-50 border-green-200'
                    : 'text-stone-500 bg-stone-50 border-stone-200'">
                <span class="inline-block w-1.5 h-1.5 rounded-full"
                    :class="modules.config.modulos.hashtag ? 'bg-green-500' : 'bg-stone-400'"></span>
                <span x-text="modules.config.modulos.hashtag ? 'Activo' : 'Inactivo'"></span>
            </span>
        </div>

        <div class="grid gap-2">
            <div>
                <label class="admin-label">Hashtag del evento</label>
                <input type="text" x-model="modules.hashtag.hashtag" @input="schedulePreview()" class="admin-input" placeholder="#MiEvento2026">
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="admin-label">Plataforma</label>
                    <select x-model="modules.hashtag.plataforma" @change="schedulePreview()" class="admin-input">
                        <option value="instagram">Instagram</option>
                        <option value="tiktok">TikTok</option>
                    </select>
                </div>
                <div>
                    <label class="admin-label">Texto del botón</label>
                    <input type="text" x-model="modules.hashtag.texto_boton" @input="schedulePreview()" class="admin-input" placeholder="Ej. Ver en Instagram">
                </div>
            </div>
        </div>
    </section>
</div>
