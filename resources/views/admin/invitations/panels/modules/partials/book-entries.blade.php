{{--
    Lista de entradas con título, fecha, texto y foto (capítulos de la historia y recuerdos) del
    «Libro de aventuras». Parámetros: code, key, max, noun (capítulo, recuerdo), textLimit, textRows,
    textLabel, textPlaceholder.
--}}
<section class="admin-card p-3 space-y-3">
    <template x-for="(entry, i) in modules.{{ $code }}.{{ $key }}" :key="i">
        <article class="rounded-xl border border-stone-200 p-3 space-y-2">
            <div class="flex items-center justify-between gap-2">
                <p class="admin-eyebrow" x-text="'{{ ucfirst($noun) }} ' + (i + 1)"></p>
                <span class="inline-flex gap-1">
                    <button type="button" @click="moveBookItem(modules.{{ $code }}.{{ $key }}, i, -1)" :disabled="i === 0"
                        class="px-2 py-1 rounded-md border border-stone-200 text-xs disabled:opacity-40" :aria-label="'Subir {{ $noun }} ' + (i + 1)">↑</button>
                    <button type="button" @click="moveBookItem(modules.{{ $code }}.{{ $key }}, i, 1)" :disabled="i === modules.{{ $code }}.{{ $key }}.length - 1"
                        class="px-2 py-1 rounded-md border border-stone-200 text-xs disabled:opacity-40" :aria-label="'Bajar {{ $noun }} ' + (i + 1)">↓</button>
                    <button type="button" @click="removeBookEntry('{{ $code }}', '{{ $key }}', i)"
                        class="px-2 py-1 rounded-md border border-red-200 text-xs text-red-700" :aria-label="'Quitar {{ $noun }} ' + (i + 1)">Quitar</button>
                </span>
            </div>

            <div class="grid gap-2 sm:grid-cols-[1fr_10rem]">
                <div>
                    <label class="admin-label" :for="'{{ $code }}-titulo-' + i">Título</label>
                    <input :id="'{{ $code }}-titulo-' + i" type="text" x-model="entry.titulo" @input="schedulePreview()" class="admin-input" maxlength="255">
                </div>
                <div>
                    <label class="admin-label" :for="'{{ $code }}-fecha-' + i">Fecha</label>
                    <input :id="'{{ $code }}-fecha-' + i" type="date" x-model="entry.fecha" @input="schedulePreview()" class="admin-input">
                </div>
            </div>

            <div>
                <label class="admin-label" :for="'{{ $code }}-texto-' + i">{{ $textLabel }}</label>
                <textarea :id="'{{ $code }}-texto-' + i" x-model="entry.texto" @input="schedulePreview()" rows="{{ $textRows }}" maxlength="{{ $textLimit }}" class="admin-input" placeholder="{{ $textPlaceholder }}"></textarea>
                <p class="mt-1 text-[11px] text-site-muted"><span x-text="{{ $textLimit }} - (entry.texto || '').length"></span> caracteres disponibles</p>
            </div>

            <div class="flex items-center gap-3">
                <div class="w-20 h-20 flex-none rounded-lg overflow-hidden border border-stone-200 bg-stone-100 grid place-items-center">
                    <img x-show="entry.foto" :src="entry.foto" alt="" class="w-full h-full object-cover" draggable="false">
                    <x-phosphor-image class="w-6 h-6 text-stone-400" x-show="!entry.foto" aria-hidden="true" />
                </div>
                <div class="flex-1 space-y-1">
                    <label class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border border-stone-200 text-xs cursor-pointer hover:border-amber-400">
                        <x-phosphor-upload-simple class="w-3.5 h-3.5" aria-hidden="true" />
                        <span x-text="entry.foto ? 'Cambiar foto' : 'Agregar foto'"></span>
                        <input type="file" accept="image/jpeg,image/png,image/webp" class="hidden" @change="uploadBookEntryPhoto('{{ $code }}', '{{ $key }}', i, $event)">
                    </label>
                    <button type="button" x-show="entry.foto" @click="clearMediaUrl(entry.foto); entry.foto = ''; schedulePreview()" class="block text-xs text-red-700">Quitar foto</button>
                    <input type="text" x-show="entry.foto" x-model="entry.alt" @input="schedulePreview()" maxlength="255" class="admin-input admin-input--sm" placeholder="Describe la foto (opcional)" :aria-label="'Descripción de la foto del {{ $noun }} ' + (i + 1)">
                </div>
            </div>
        </article>
    </template>

    <button type="button" @click="addBookEntry('{{ $code }}', '{{ $key }}', {{ $max }})"
        :disabled="(modules.{{ $code }}.{{ $key }} || []).length >= {{ $max }}"
        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border-2 border-dashed border-stone-200 hover:border-amber-400 text-sm text-stone-600 disabled:opacity-50">
        <x-phosphor-plus class="w-4 h-4" aria-hidden="true" />
        <span x-text="(modules.{{ $code }}.{{ $key }} || []).length >= {{ $max }} ? 'Llegaste al máximo de {{ $max }}' : 'Agregar {{ $noun }}'"></span>
    </button>
</section>
