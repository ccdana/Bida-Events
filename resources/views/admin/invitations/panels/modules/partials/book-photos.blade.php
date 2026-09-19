{{--
    Fotos sueltas de un módulo del «Libro de aventuras» (collage, marcos, memoria): subir, recortar,
    ordenar, describir y quitar. Parámetros: code, max, altPlaceholder, altHelp.
--}}
<section class="admin-card p-3 space-y-2">
    <p class="admin-eyebrow mb-1">Subir fotos</p>
    <label class="flex items-center justify-center gap-2 px-4 py-3 rounded-xl border-2 border-dashed border-stone-200 hover:border-amber-400 hover:bg-amber-50/30 cursor-pointer transition text-sm text-stone-600"
        :class="mediaUploading || (modules.{{ $code }}.fotos || []).length >= {{ $max }} ? 'opacity-60 pointer-events-none' : ''">
        <x-phosphor-plus class="w-4 h-4" aria-hidden="true" />
        <span x-text="(modules.{{ $code }}.fotos || []).length >= {{ $max }} ? 'Llegaste al máximo de {{ $max }} fotos' : 'Agregar fotos (múltiple)'"></span>
        <input type="file" accept="image/jpeg,image/png,image/webp" multiple class="hidden" @change="uploadBookPhotos('{{ $code }}', $event, {{ $max }})">
    </label>
    <p class="text-xs text-stone-500">Hasta {{ $max }} fotos. Se suben al guardar; antes puedes recortarlas.</p>
</section>

<section class="admin-card p-3 space-y-2" x-show="(modules.{{ $code }}.fotos || []).length" x-cloak>
    <p class="admin-eyebrow mb-1"><span x-text="(modules.{{ $code }}.fotos || []).length"></span> fotos</p>
    <p class="text-xs text-stone-500">{{ $altHelp }}</p>
    <div class="grid grid-cols-2 gap-3">
        <template x-for="(foto, i) in modules.{{ $code }}.fotos" :key="photoUrl(foto) + i">
            <div class="space-y-1">
                <div class="relative group aspect-square rounded-xl overflow-hidden border border-stone-200 bg-stone-100">
                    <img :src="photoUrl(foto)" :alt="photoAlt(foto)" class="w-full h-full object-cover select-none" draggable="false">
                    <div class="absolute inset-1 flex flex-col justify-between">
                        <div class="flex justify-between">
                            <span class="inline-flex gap-1">
                                <button type="button" @click.stop="moveBookItem(modules.{{ $code }}.fotos, i, -1)" :disabled="i === 0"
                                    class="w-6 h-6 rounded-full bg-white/95 text-stone-700 text-xs shadow disabled:opacity-40" :aria-label="'Mover la foto ' + (i + 1) + ' antes'">‹</button>
                                <button type="button" @click.stop="moveBookItem(modules.{{ $code }}.fotos, i, 1)" :disabled="i === modules.{{ $code }}.fotos.length - 1"
                                    class="w-6 h-6 rounded-full bg-white/95 text-stone-700 text-xs shadow disabled:opacity-40" :aria-label="'Mover la foto ' + (i + 1) + ' después'">›</button>
                            </span>
                            <button type="button" @click.stop="removeBookPhoto('{{ $code }}', i)"
                                class="w-6 h-6 rounded-full bg-red-600 text-white text-xs shadow" :aria-label="'Quitar la foto ' + (i + 1)">×</button>
                        </div>
                        <button type="button" x-show="String(photoUrl(foto)).startsWith('blob:')"
                            @click.stop="openImageCropper(photoUrl(foto), '{{ $code }}')"
                            class="inline-flex items-center justify-center gap-1 px-1.5 py-1 rounded-md bg-white/95 text-[11px] font-medium text-stone-700 shadow">
                            <x-phosphor-crop class="w-3.5 h-3.5" aria-hidden="true" />
                            Recortar
                        </button>
                    </div>
                </div>
                <input type="text" maxlength="255" class="admin-input admin-input--sm"
                    :value="photoAlt(foto)"
                    @change="setPhotoAlt(modules.{{ $code }}.fotos, i, $event.target.value)"
                    :aria-label="'Descripción de la foto ' + (i + 1)"
                    placeholder="{{ $altPlaceholder }}">
            </div>
        </template>
    </div>
</section>
