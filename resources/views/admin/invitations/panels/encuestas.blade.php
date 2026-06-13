<div x-show="activeTab === 'encuestas'" x-cloak class="space-y-4">
    <section class="admin-card space-y-4">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="admin-eyebrow">Encuestas</p>
                <h2 class="font-serif text-xl text-stone-950">Interactivo</h2>
                <p class="mt-1 text-sm text-stone-500">Crea preguntas de opción única, escala, sí/no y emoji en un bloque independiente.</p>
            </div>
            <div class="flex items-center gap-3">
                <label class="admin-toggle-row">
                    <input type="checkbox" x-model="modules.config.modulos.encuestas" class="rounded border-stone-300 text-amber-600 focus:ring-amber-500">
                    <span class="text-stone-700">Activo</span>
                </label>
                <div class="flex flex-wrap gap-2">
                    <button type="button" @click="addEncuesta('single')" class="admin-primary-button">+ Opción única</button>
                    <button type="button" @click="addEncuesta('rating')" class="admin-link-button">+ Escala</button>
                    <button type="button" @click="addEncuesta('yesno')" class="admin-link-button">+ Sí / No</button>
                    <button type="button" @click="addEncuesta('emoji')" class="admin-link-button">+ Emoji</button>
                </div>
            </div>
        </div>
        <input type="text" x-model="modules.encuestas.titulo" class="admin-input" placeholder="Título sección">

        <template x-for="(poll, pi) in modules.encuestas.preguntas" :key="poll.id">
            <section class="rounded-2xl border border-stone-200 bg-stone-50 p-4 space-y-3">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <span class="admin-context-badge is-live">Encuesta</span>
                    <button type="button" @click="removeEncuesta(pi)" class="text-xs text-red-600 hover:text-red-700 self-start sm:self-auto">Eliminar</button>
                </div>
                <div class="grid gap-3 md:grid-cols-[1.2fr_0.8fr]">
                    <input type="text" x-model="poll.pregunta" class="admin-input" placeholder="Pregunta">
                    <select x-model="poll.tipo" @change="setPollType(poll, $event.target.value)" class="admin-input">
                        <option value="single">Opción única</option>
                        <option value="rating">Escala</option>
                        <option value="yesno">Sí / No</option>
                        <option value="emoji">Emoji</option>
                    </select>
                </div>
                <input type="text" x-model="poll.id" class="admin-input text-xs font-mono" placeholder="ID único (color-vestido)">

                <template x-if="poll.tipo === 'rating'">
                    <div class="grid gap-2 md:grid-cols-5">
                        <template x-for="(op, oi) in poll.opciones" :key="oi">
                            <button type="button" class="rounded-2xl border border-stone-200 bg-white p-4 text-center text-sm font-medium text-stone-700">
                                <span x-text="op"></span>
                            </button>
                        </template>
                    </div>
                </template>

                <template x-if="poll.tipo === 'yesno'">
                    <div class="grid gap-2 md:grid-cols-2">
                        <template x-for="(op, oi) in poll.opciones" :key="oi">
                            <input type="text" x-model="poll.opciones[oi]" class="admin-input" :placeholder="'Respuesta ' + (oi + 1)">
                        </template>
                    </div>
                </template>

                <template x-if="poll.tipo === 'emoji'">
                    <div class="grid gap-2 md:grid-cols-5">
                        <template x-for="(op, oi) in poll.opciones" :key="oi">
                            <input type="text" x-model="poll.opciones[oi]" class="admin-input text-center text-xl" :placeholder="'😀'">
                        </template>
                    </div>
                </template>

                <template x-if="poll.tipo === 'single'">
                    <div class="space-y-2">
                        <template x-for="(op, oi) in poll.opciones" :key="oi">
                            <div class="flex gap-2">
                                <input type="text" x-model="poll.opciones[oi]" class="admin-input flex-1" :placeholder="'Opción ' + (oi+1)">
                                <button type="button" @click="removeOpcion(poll, oi)" class="text-red-500 text-xs px-2">×</button>
                            </div>
                        </template>
                        <div class="flex items-center gap-3">
                            <button type="button" @click="addOpcion(poll)" class="admin-link-button">+ Opción</button>
                            <span class="text-xs text-stone-400">Agrega tantas respuestas como necesites.</span>
                        </div>
                    </div>
                </template>
            </section>
        </template>
    </section>
</div>
