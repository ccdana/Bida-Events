<div x-show="activeTab === 'interactivo'" x-cloak class="space-y-4">
    <div class="admin-card space-y-3">
        <h2 class="font-serif text-lg text-stone-900">Playlist colaborativa</h2>
        <input type="text" x-model="modules.playlist.titulo" class="admin-input" placeholder="Título">
        <textarea x-model="modules.playlist.descripcion" rows="2" class="admin-input" placeholder="Descripción"></textarea>
        <input type="text" x-model="modules.playlist.placeholder" class="admin-input" placeholder="Placeholder del input">
    </div>
    <div class="admin-card space-y-3">
        <h2 class="font-serif text-lg text-stone-900">Hashtag</h2>
        <input type="text" x-model="modules.hashtag.hashtag" class="admin-input" placeholder="#MiEvento2026">
        <select x-model="modules.hashtag.plataforma" class="admin-input">
            <option value="instagram">Instagram</option>
            <option value="tiktok">TikTok</option>
        </select>
        <input type="text" x-model="modules.hashtag.texto_boton" class="admin-input" placeholder="Texto del botón">
    </div>
    <div class="admin-card space-y-3">
        <div class="flex justify-between items-center">
            <h2 class="font-serif text-lg text-stone-900">Encuestas</h2>
            <button type="button" @click="addEncuesta()" class="text-xs px-3 py-1.5 bg-stone-900 text-white rounded-lg">+ Encuesta</button>
        </div>
        <input type="text" x-model="modules.encuestas.titulo" class="admin-input" placeholder="Título sección">
        <template x-for="(poll, pi) in modules.encuestas.preguntas" :key="poll.id">
            <div class="rounded-xl border border-stone-200 p-4 space-y-2 bg-stone-50/50">
                <div class="flex justify-between">
                    <span class="text-xs text-stone-500">Encuesta</span>
                    <button type="button" @click="removeEncuesta(pi)" class="text-xs text-red-600">Eliminar</button>
                </div>
                <input type="text" x-model="poll.pregunta" class="admin-input" placeholder="Pregunta">
                <input type="text" x-model="poll.id" class="admin-input text-xs font-mono" placeholder="ID único (color-vestido)">
                <template x-for="(op, oi) in poll.opciones" :key="oi">
                    <div class="flex gap-2">
                        <input type="text" x-model="poll.opciones[oi]" class="admin-input flex-1" :placeholder="'Opción ' + (oi+1)">
                        <button type="button" @click="removeOpcion(poll, oi)" class="text-red-500 text-xs px-2">×</button>
                    </div>
                </template>
                <button type="button" @click="addOpcion(poll)" class="text-xs text-amber-800">+ Opción</button>
            </div>
        </template>
    </div>
</div>
