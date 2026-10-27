{{--
    Lista de partes: las partes de la invitación, solo para encenderlas o apagarlas. Al encender una nace
    con su contenido de ejemplo (App\Support\SampleContent) y la vista previa baja hasta ella; desde ahí
    se cambia tocando cada texto o foto. Cada fila: ícono, nombre, el nivel en 0–3 puntos dorados («Hazlo tú»),
    el «?» (para qué se sugiere usarla) y el interruptor; sin párrafos ni «Apagada».
    En el celular se abre como cajón desde «Partes»; en computadora es la columna junto al riel (editor/rail),
    que se pliega. Lógica en resources/js/admin/editor.js (togglePart, goToPart).
--}}
@php
    // Un ícono suelto por parte (por el id de su pestaña); una parte nueva usa el de «default»
    $partIcons = collect([
        'hero' => 'image-square', 'ubicacion' => 'map-pin', 'itinerario' => 'list-bullets', 'dress' => 't-shirt',
        'destacados' => 'star', 'galeria' => 'images', 'video' => 'video-camera', 'musica' => 'music-notes',
        'playlist' => 'playlist', 'hashtag' => 'hash', 'encuestas' => 'chart-bar', 'regalos' => 'gift',
        'rsvp_whatsapp' => 'whatsapp-logo', 'rsvp' => 'qr-code', 'countdown' => 'hourglass', 'agendar' => 'calendar-plus',
        'fotomural' => 'camera', 'post_evento' => 'hand-heart',
        // Tarjetas y libro
        'relato' => 'book-open-text', 'dedicatoria' => 'envelope-simple', 'juntos_desde' => 'calendar-heart', 'respuesta' => 'chat-circle-text',
        'historia' => 'scroll', 'recuerdos' => 'image', 'collage' => 'flower', 'marcos' => 'frame-corners', 'memoria' => 'puzzle-piece',
        'aventuras' => 'compass',
        'default' => 'sparkle',
    ])->map(fn (string $name) => svg('phosphor-'.$name.'-light', 'ed-part__glyph', ['aria-hidden' => 'true'])->toHtml());
@endphp
<aside class="ed-modules admin-editor-scroll" :class="{ 'is-open': modulesOpen, 'is-collapsed': !partsOpen }" aria-label="Partes de tu invitación"
    x-data="{ partIcons: @js($partIcons) }">
    <div class="ed-modules__head">
        <h2 class="ed-modules__title">Partes</h2>
        <button type="button" class="ed-modules__close" @click="modulesOpen = false" aria-label="Cerrar" title="Cerrar">
            <x-phosphor-x-light aria-hidden="true" />
        </button>
    </div>

    <template x-for="group in partGroups" :key="group.id">
        <section class="ed-modules__group">
            <h3 class="ed-modules__label" x-text="group.label"></h3>
            <template x-for="tab in group.tabs" :key="tab.id">
                <div class="ed-part" :class="{ 'is-on': isTabEnabled(tab), 'is-locked': !!packageLockLabel(tab), 'is-pending': isTabEnabled(tab) && examplesOf(tab) > 0 }">
                    <div class="ed-part__row">
                        <button type="button" class="ed-part__name" @click="goToPart(tab)"
                            :title="packageLockLabel(tab) || (isTabEnabled(tab) ? 'Ver ' + tab.label + ' en la invitación' : 'Encender ' + tab.label)">
                            <span class="ed-part__icon" aria-hidden="true" x-html="partIcons[tab.id] ?? partIcons.default"></span>
                            <span class="ed-part__label" x-text="tab.label"></span>

                            {{-- Lo que falta, sin palabras: cosas de ejemplo por cambiar (un número dorado), un dato pendiente o la parte que no se verá --}}
                            <span class="ed-part__pending" x-show="isTabEnabled(tab) && examplesOf(tab) > 0" x-cloak x-text="examplesOf(tab)"
                                :title="partStatus(tab)"></span>
                            <span class="sr-only" x-show="isTabEnabled(tab) && examplesOf(tab) > 0" x-cloak x-text="partStatus(tab)"></span>
                            <x-phosphor-warning-circle-light class="ed-part__issue" x-show="isTabEnabled(tab) && !examplesOf(tab) && tabStatus(tab) === 'issue'" x-cloak aria-hidden="true" />
                            <span class="sr-only" x-show="isTabEnabled(tab) && !examplesOf(tab) && tabStatus(tab) === 'issue'" x-cloak x-text="partStatus(tab)"></span>
                            <x-phosphor-lock-simple-light class="ed-part__lock" x-show="!!packageLockLabel(tab)" x-cloak aria-hidden="true" />
                            <span class="sr-only" x-show="!!packageLockLabel(tab)" x-cloak x-text="packageLockLabel(tab)"></span>

                            {{-- «Hazlo tú»: desde qué nivel viene la parte, en 1–3 puntos dorados (las gratuitas no llevan); en rojo si no se verá con el nivel pagado --}}
                            <span class="ed-dots" x-show="diyPartLevel(tab) > 0" x-cloak :data-level="diyPartLevel(tab)"
                                :class="{ 'is-above': diyAbovePaid(tab) }" :title="diyAbovePaid(tab) ? partStatus(tab) : 'Nivel ' + diyPartTier(tab)" aria-hidden="true"><i></i><i></i><i></i></span>
                            <span class="sr-only" x-show="diyPartLevel(tab) > 0" x-cloak x-text="'Nivel ' + diyPartTier(tab)"></span>
                        </button>
                        <button type="button" class="ed-help-btn" @click="helpOpen = helpOpen === 'part:' + tab.id ? null : 'part:' + tab.id"
                            :aria-expanded="(helpOpen === 'part:' + tab.id).toString()"
                            :aria-label="'¿Para qué sirve ' + tab.label + '?'" title="¿Para qué sirve?">
                            <x-phosphor-question-light aria-hidden="true" />
                        </button>
                        <button type="button" role="switch" class="adm-switch"
                            @click="togglePart(tab)"
                            :disabled="!!packageLockLabel(tab)"
                            :class="isTabEnabled(tab) ? 'is-on' : ''"
                            :aria-checked="isTabEnabled(tab).toString()"
                            :aria-label="'Mostrar ' + tab.label + ' en la invitación'"></button>
                    </div>
                    <p class="ed-part__help" x-show="helpOpen === 'part:' + tab.id" x-cloak x-transition.opacity x-text="partHelp(tab)"></p>
                </div>
            </template>
        </section>
    </template>
</aside>
<div class="ed-modules__backdrop" x-show="modulesOpen" x-cloak x-transition.opacity @click="modulesOpen = false"></div>
