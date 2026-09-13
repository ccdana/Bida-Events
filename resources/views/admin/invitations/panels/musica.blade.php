<div x-show="activeTab === 'musica'" x-cloak class="space-y-2">
    @include('admin.partials.panel-intro', [
        'eyebrow' => 'Música',
        'title' => 'Canción de fondo',
        'description' => 'una barra fija en la parte inferior con botón de reproducir, el título y el artista.',
        'moduleKey' => 'musica',
    ])

    <section class="admin-card p-3 space-y-3">

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

        <div class="pt-1 flex items-center justify-between gap-3 border-t border-stone-100 mt-2">
            <span class="min-w-0">
                <span class="block text-sm font-semibold text-stone-700">Reproducir automáticamente</span>
                <span class="block text-[11px] leading-relaxed text-stone-400">Los navegadores bloquean el sonido automático: la música empezará con el primer toque del invitado.</span>
            </span>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" x-model="modules.musica.autoplay" @change="schedulePreview()" class="sr-only peer">
                <div class="w-9 h-5 bg-stone-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-stone-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-amber-500"></div>
            </label>
        </div>
    </section>
</div>
