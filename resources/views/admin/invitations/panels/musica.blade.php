<div x-show="activeTab === 'musica'" x-cloak class="space-y-2">
    <section class="admin-card p-3 space-y-3">
        <div class="flex items-center justify-between gap-2">
            <div class="min-w-0">
                <p class="admin-eyebrow mb-0.5">Música</p>
                <p class="text-xs text-stone-600 truncate">Audio de fondo de la invitación</p>
            </div>
            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md border text-xs font-semibold shrink-0"
                :class="modules.config.modulos.musica
                    ? 'text-green-700 bg-green-50 border-green-200'
                    : 'text-stone-500 bg-stone-50 border-stone-200'">
                <span class="inline-block w-1.5 h-1.5 rounded-full"
                    :class="modules.config.modulos.musica ? 'bg-green-500' : 'bg-stone-400'"></span>
                <span x-text="modules.config.modulos.musica ? 'Activo' : 'Inactivo'"></span>
            </span>
        </div>

        <div class="grid gap-2">
            <div>
                <label class="admin-label">Título</label>
                <input type="text" x-model="modules.musica.titulo" @input="schedulePreview()" class="admin-input" placeholder="Ej. Nuestra Canción">
            </div>
            <div>
                <label class="admin-label">Artista / descripción</label>
                <input type="text" x-model="modules.musica.artista" @input="schedulePreview()" class="admin-input" placeholder="Ej. Frank Sinatra">
            </div>
        </div>

        <div>
            @include('admin.partials.cloudinary-upload', [
                'label' => 'Archivo de audio (MP3)',
                'type' => 'audio',
                'context' => 'musica',
                'accept' => 'audio/mpeg,audio/mp3,audio/wav,audio/ogg,audio/aac,audio/mp4',
                'previewExpr' => 'modules.musica.audio_url',
            ])
        </div>

        <div class="pt-1 flex items-center justify-between border-t border-stone-100 mt-2">
            <span class="text-sm font-semibold text-stone-700">Autoplay</span>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" x-model="modules.musica.autoplay" @change="schedulePreview()" class="sr-only peer">
                <div class="w-9 h-5 bg-stone-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-stone-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-amber-500"></div>
            </label>
        </div>
    </section>
</div>
