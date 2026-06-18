<div x-show="activeTab === 'encuestas'" x-cloak class="space-y-2">
    <section class="admin-card p-3 space-y-3">
        <div class="flex items-center justify-between gap-2">
            <div class="min-w-0">
                <p class="admin-eyebrow mb-0.5">Encuestas</p>
                <p class="text-xs text-stone-600 truncate">Preguntas interactivas para tus invitados</p>
            </div>
            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md border text-xs font-semibold shrink-0"
                :class="modules.config.modulos.encuestas
                    ? 'text-green-700 bg-green-50 border-green-200'
                    : 'text-stone-500 bg-stone-50 border-stone-200'">
                <span class="inline-block w-1.5 h-1.5 rounded-full"
                    :class="modules.config.modulos.encuestas ? 'bg-green-500' : 'bg-stone-400'"></span>
                <span x-text="modules.config.modulos.encuestas ? 'Activo' : 'Inactivo'"></span>
            </span>
        </div>

        <div>
            <label class="admin-label">Título de la sección</label>
            <input type="text" x-model="modules.encuestas.titulo" @input="schedulePreview()"
                class="admin-input" placeholder="Ej. Cuéntanos más, Tu opinión importa…">
        </div>

        <div>
            <label class="admin-label mb-3">Agregar nueva pregunta</label>
            <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                <button type="button" @click="addEncuesta('single')"
                    class="flex flex-col items-center gap-2 rounded-xl border border-stone-200 bg-stone-50 p-3 text-center hover:border-amber-300 hover:bg-amber-50 transition group">
                    <svg class="w-6 h-6 text-stone-500 group-hover:text-amber-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <rect x="3" y="5" width="18" height="14" rx="2"/><path d="M7 9h10M7 13h6" stroke-linecap="round"/>
                    </svg>
                    <div>
                        <p class="text-xs font-semibold text-stone-700 group-hover:text-amber-800">Opción única</p>
                        <p class="text-[10px] text-stone-400">Elige una respuesta</p>
                    </div>
                </button>
                <button type="button" @click="addEncuesta('rating')"
                    class="flex flex-col items-center gap-2 rounded-xl border border-stone-200 bg-stone-50 p-3 text-center hover:border-amber-300 hover:bg-amber-50 transition group">
                    <svg class="w-6 h-6 text-stone-500 group-hover:text-amber-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <polygon points="12 2 15 9 22 9 17 14 19 22 12 18 5 22 7 14 2 9 9 9" stroke-linejoin="round"/>
                    </svg>
                    <div>
                        <p class="text-xs font-semibold text-stone-700 group-hover:text-amber-800">Escala</p>
                        <p class="text-[10px] text-stone-400">Calificación 1–5</p>
                    </div>
                </button>
                <button type="button" @click="addEncuesta('yesno')"
                    class="flex flex-col items-center gap-2 rounded-xl border border-stone-200 bg-stone-50 p-3 text-center hover:border-amber-300 hover:bg-amber-50 transition group">
                    <svg class="w-6 h-6 text-stone-500 group-hover:text-amber-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    <div>
                        <p class="text-xs font-semibold text-stone-700 group-hover:text-amber-800">Sí / No</p>
                        <p class="text-[10px] text-stone-400">Dos opciones</p>
                    </div>
                </button>
                <button type="button" @click="addEncuesta('emoji')"
                    class="flex flex-col items-center gap-2 rounded-xl border border-stone-200 bg-stone-50 p-3 text-center hover:border-amber-300 hover:bg-amber-50 transition group">
                    <svg class="w-6 h-6 text-stone-500 group-hover:text-amber-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <circle cx="12" cy="12" r="9"/><path d="M8.5 14s1.5 2 3.5 2 3.5-2 3.5-2" stroke-linecap="round"/><line x1="9" y1="9" x2="9.01" y2="9" stroke-width="2.5" stroke-linecap="round"/><line x1="15" y1="9" x2="15.01" y2="9" stroke-width="2.5" stroke-linecap="round"/>
                    </svg>
                    <div>
                        <p class="text-xs font-semibold text-stone-700 group-hover:text-amber-800">Emoji</p>
                        <p class="text-[10px] text-stone-400">Reacción con emojis</p>
                    </div>
                </button>
            </div>
        </div>
    </section>

    {{-- Estado vacío --}}
    <template x-if="modules.encuestas.preguntas.length === 0">
        <div class="flex flex-col items-center justify-center gap-3 rounded-2xl border-2 border-dashed border-stone-200 bg-stone-50 py-12 px-6 text-center">
            <svg class="w-10 h-10 text-stone-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <rect x="4" y="12" width="4" height="8" rx="1"/><rect x="10" y="8" width="4" height="12" rx="1"/><rect x="16" y="4" width="4" height="16" rx="1"/>
            </svg>
            <p class="text-sm font-medium text-stone-500">Aún no hay preguntas</p>
            <p class="text-xs text-stone-400">Usa los botones de arriba para agregar tu primera encuesta.</p>
        </div>
    </template>

    {{-- Lista de encuestas --}}
    <template x-for="(poll, pi) in modules.encuestas.preguntas" :key="poll.id">
        <section class="admin-card space-y-4">
            {{-- Cabecera de la encuesta --}}
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg"
                        :class="{
                            'bg-amber-100 text-amber-700': poll.tipo === 'single',
                            'bg-yellow-100 text-yellow-700': poll.tipo === 'rating',
                            'bg-blue-100 text-blue-700': poll.tipo === 'yesno',
                            'bg-pink-100 text-pink-700': poll.tipo === 'emoji',
                        }">
                        <template x-if="poll.tipo === 'single'">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M7 9h10M7 13h6" stroke-linecap="round"/></svg>
                        </template>
                        <template x-if="poll.tipo === 'rating'">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><polygon points="12 2 15 9 22 9 17 14 19 22 12 18 5 22 7 14 2 9 9 9" stroke-linejoin="round"/></svg>
                        </template>
                        <template x-if="poll.tipo === 'yesno'">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </template>
                        <template x-if="poll.tipo === 'emoji'">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="9"/><path d="M8.5 14s1.5 2 3.5 2 3.5-2 3.5-2" stroke-linecap="round"/><line x1="9" y1="9" x2="9.01" y2="9" stroke-width="2.5" stroke-linecap="round"/><line x1="15" y1="9" x2="15.01" y2="9" stroke-width="2.5" stroke-linecap="round"/></svg>
                        </template>
                    </span>
                    <span class="text-xs font-bold uppercase tracking-wider"
                        :class="{
                            'text-amber-700': poll.tipo === 'single',
                            'text-yellow-700': poll.tipo === 'rating',
                            'text-blue-700': poll.tipo === 'yesno',
                            'text-pink-700': poll.tipo === 'emoji',
                        }"
                        x-text="poll.tipo === 'single' ? 'Opción única' : poll.tipo === 'rating' ? 'Escala' : poll.tipo === 'yesno' ? 'Sí / No' : 'Emoji'">
                    </span>
                    <span class="text-stone-300">·</span>
                    <span class="text-xs text-stone-400">Pregunta <span x-text="pi + 1"></span></span>
                </div>
                <button type="button" @click="removeEncuesta(pi)"
                    class="flex items-center gap-1 rounded-lg px-2.5 py-1 text-[11px] font-medium text-red-500 hover:bg-red-50 hover:text-red-600 transition">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Eliminar
                </button>
            </div>

            {{-- Pregunta y tipo --}}
            <div class="grid gap-3 md:grid-cols-[1.4fr_0.6fr]">
                <div>
                    <label class="admin-label">Pregunta</label>
                    <input type="text" x-model="poll.pregunta" @input="schedulePreview()"
                        class="admin-input" placeholder="Ej. ¿Cuál es tu canción favorita?">
                </div>
                <div>
                    <label class="admin-label">Tipo</label>
                    <select x-model="poll.tipo" @change="setPollType(poll, $event.target.value)" class="admin-input">
                        <option value="single">Opción única</option>
                        <option value="rating">Escala 1–5</option>
                        <option value="yesno">Sí / No</option>
                        <option value="emoji">Reacción</option>
                    </select>
                </div>
            </div>

            {{-- ID único --}}
            <div>
                <label class="admin-label">ID único <span class="normal-case text-stone-400">(para identificar respuestas)</span></label>
                <input type="text" x-model="poll.id" class="admin-input font-mono text-xs"
                    placeholder="cancion-favorita, color-vestido…">
            </div>

            {{-- Opciones según tipo --}}

            {{-- ESCALA: solo preview --}}
            <template x-if="poll.tipo === 'rating'">
                <div>
                    <label class="admin-label mb-2">Vista previa de opciones</label>
                    <div class="flex gap-2">
                        <template x-for="(op, oi) in poll.opciones" :key="oi">
                            <div class="flex-1 flex items-center justify-center rounded-xl border border-stone-200 bg-stone-50 py-3 text-sm font-semibold text-stone-600">
                                <span x-text="op"></span>
                            </div>
                        </template>
                    </div>
                    <p class="mt-2 text-[11px] text-stone-400">La escala de 1 a 5 se genera automáticamente.</p>
                </div>
            </template>

            {{-- SÍ / NO: editable --}}
            <template x-if="poll.tipo === 'yesno'">
                <div>
                    <label class="admin-label mb-2">Opciones</label>
                    <div class="grid grid-cols-2 gap-2">
                        <template x-for="(op, oi) in poll.opciones" :key="oi">
                            <div>
                                <label class="admin-label" x-text="oi === 0 ? 'Opción positiva' : 'Opción negativa'"></label>
                                <input type="text" x-model="poll.opciones[oi]" @input="schedulePreview()"
                                    class="admin-input" :placeholder="oi === 0 ? 'Sí' : 'No'">
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            {{-- REACCIÓN / EMOJI: editable --}}
            <template x-if="poll.tipo === 'emoji'">
                <div>
                    <label class="admin-label mb-2">Opciones de reacción</label>
                    <p class="mb-2 text-[11px] text-stone-400">Escribe un emoji por campo. Se muestran como botones de reacción para los invitados.</p>
                    <div class="flex gap-2">
                        <template x-for="(op, oi) in poll.opciones" :key="oi">
                            <input type="text" x-model="poll.opciones[oi]" @input="schedulePreview()"
                                class="admin-input flex-1 text-center text-xl px-1"
                                :placeholder="['\ud83d\ude0d','\u2728','\ud83c\udf89','\ud83d\udc96','\ud83d\udd25'][oi] ?? '\ud83d\ude00'">
                        </template>
                    </div>
                </div>
            </template>

            {{-- OPCIÓN ÚNICA: lista editable --}}
            <template x-if="poll.tipo === 'single'">
                <div class="space-y-2">
                    <label class="admin-label">Opciones de respuesta</label>
                    <template x-for="(op, oi) in poll.opciones" :key="oi">
                        <div class="flex items-center gap-2">
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full border border-stone-300 bg-white text-[10px] font-bold text-stone-500"
                                x-text="String.fromCharCode(65 + oi)">
                            </span>
                            <input type="text" x-model="poll.opciones[oi]" @input="schedulePreview()"
                                class="admin-input flex-1" :placeholder="'Opción ' + (oi + 1)">
                            <button type="button" @click="removeOpcion(poll, oi)"
                                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg text-stone-400 hover:bg-red-50 hover:text-red-500 transition"
                                title="Eliminar opción">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </template>
                    <button type="button" @click="addOpcion(poll)"
                        class="flex w-full items-center justify-center gap-2 rounded-xl border-2 border-dashed border-stone-200 bg-stone-50 py-2.5 text-xs font-semibold text-stone-500 hover:border-stone-300 hover:bg-stone-100 hover:text-stone-700 transition">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        Agregar opción
                    </button>
                </div>
            </template>
        </section>
    </template>
</div>
