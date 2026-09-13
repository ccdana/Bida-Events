<div x-show="activeTab === 'encuestas'" x-cloak class="space-y-2">
    @include('admin.partials.panel-intro', [
        'eyebrow' => 'Encuestas',
        'title' => 'Preguntas para votar',
        'description' => 'cada pregunta con sus opciones como botones. Al tocar una, el voto se guarda y aparecen los porcentajes de todos. Opción única y Sí/No se ven en lista; Escala y Emoji, en fila.',
        'tip' => 'No cambies el «ID único» de una pregunta después de publicar: los votos ya recibidos están asociados a ese ID.',
        'moduleKey' => 'encuestas',
        'countExpr' => '`${modules.encuestas.preguntas.length} preguntas`',
    ])

    <section class="admin-card p-3 space-y-3">

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
                    <x-phosphor-list-bullets class="w-6 h-6 text-stone-500 group-hover:text-amber-700" aria-hidden="true" />
                    <div>
                        <p class="text-xs font-semibold text-stone-700 group-hover:text-amber-800">Opción única</p>
                        <p class="text-[10px] text-stone-400">Elige una respuesta</p>
                    </div>
                </button>
                <button type="button" @click="addEncuesta('rating')"
                    class="flex flex-col items-center gap-2 rounded-xl border border-stone-200 bg-stone-50 p-3 text-center hover:border-amber-300 hover:bg-amber-50 transition group">
                    <x-phosphor-star class="w-6 h-6 text-stone-500 group-hover:text-amber-700" aria-hidden="true" />
                    <div>
                        <p class="text-xs font-semibold text-stone-700 group-hover:text-amber-800">Escala</p>
                        <p class="text-[10px] text-stone-400">Calificación 1–5</p>
                    </div>
                </button>
                <button type="button" @click="addEncuesta('yesno')"
                    class="flex flex-col items-center gap-2 rounded-xl border border-stone-200 bg-stone-50 p-3 text-center hover:border-amber-300 hover:bg-amber-50 transition group">
                    <x-phosphor-check class="w-6 h-6 text-stone-500 group-hover:text-amber-700" aria-hidden="true" />
                    <div>
                        <p class="text-xs font-semibold text-stone-700 group-hover:text-amber-800">Sí / No</p>
                        <p class="text-[10px] text-stone-400">Dos opciones</p>
                    </div>
                </button>
                <button type="button" @click="addEncuesta('emoji')"
                    class="flex flex-col items-center gap-2 rounded-xl border border-stone-200 bg-stone-50 p-3 text-center hover:border-amber-300 hover:bg-amber-50 transition group">
                    <x-phosphor-smiley class="w-6 h-6 text-stone-500 group-hover:text-amber-700" aria-hidden="true" />
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
            <x-phosphor-chart-bar-light class="w-10 h-10 text-stone-300" aria-hidden="true" />
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
                            <x-phosphor-list-bullets class="w-4 h-4" aria-hidden="true" />
                        </template>
                        <template x-if="poll.tipo === 'rating'">
                            <x-phosphor-star class="w-4 h-4" aria-hidden="true" />
                        </template>
                        <template x-if="poll.tipo === 'yesno'">
                            <x-phosphor-check class="w-4 h-4" aria-hidden="true" />
                        </template>
                        <template x-if="poll.tipo === 'emoji'">
                            <x-phosphor-smiley class="w-4 h-4" aria-hidden="true" />
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
                    <x-phosphor-trash class="w-3.5 h-3.5" aria-hidden="true" />
                    Eliminar
                </button>
            </div>

            {{-- Pregunta y tipo --}}
            <div class="space-y-3">
                <div>
                    <label class="admin-label">Pregunta</label>
                    <input type="text" x-model="poll.pregunta" @input="schedulePreview()"
                        class="admin-input" placeholder="Ej. ¿Cuál es tu canción favorita?">
                </div>
                
                <div x-data="{ open: false }" class="admin-accordion">
                    <button type="button" @click="open = !open" class="admin-accordion-trigger">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-stone-900 text-left">Tipo de encuesta</p>
                            <p class="text-xs text-stone-500 text-left truncate" 
                                x-text="poll.tipo === 'single' ? 'Opción única' : poll.tipo === 'rating' ? 'Escala 1–5' : poll.tipo === 'yesno' ? 'Sí / No' : 'Reacción'">
                            </p>
                        </div>
                        <x-phosphor-caret-down class="w-4 h-4 flex-shrink-0 transition-transform text-stone-500" x-bind:class="{ 'rotate-180': open }" aria-hidden="true" />
                    </button>
                    <div x-show="open" class="admin-accordion-panel space-y-1">
                        <template x-for="opt in [
                            {v: 'single', l: 'Opción única'},
                            {v: 'rating', l: 'Escala 1–5'},
                            {v: 'yesno', l: 'Sí / No'},
                            {v: 'emoji', l: 'Reacción'}
                        ]" :key="opt.v">
                            <button type="button"
                                @click="setPollType(poll, opt.v); open = false"
                                class="admin-accordion-option"
                                :class="poll.tipo === opt.v ? 'is-selected' : ''"
                                x-text="opt.l">
                            </button>
                        </template>
                    </div>
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
                                <x-phosphor-x class="w-3.5 h-3.5" aria-hidden="true" />
                            </button>
                        </div>
                    </template>
                    <button type="button" @click="addOpcion(poll)"
                        class="flex w-full items-center justify-center gap-2 rounded-xl border-2 border-dashed border-stone-200 bg-stone-50 py-2.5 text-xs font-semibold text-stone-500 hover:border-stone-300 hover:bg-stone-100 hover:text-stone-700 transition">
                        <x-phosphor-plus class="w-3.5 h-3.5" aria-hidden="true" />
                        Agregar opción
                    </button>
                </div>
            </template>
        </section>
    </template>
</div>
