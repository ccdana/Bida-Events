{{--
    Barra lateral: las partes de la invitación, solo para encenderlas o apagarlas. Al encender una nace
    con su contenido de ejemplo (App\Support\SampleContent) y la vista previa baja hasta ella; desde ahí
    se cambia tocando cada texto o foto. El «?» de cada fila explica para qué se sugiere usarla.
    En el celular se abre como cajón desde «Partes». Lógica en editor/script (togglePart, goToPart).
--}}
<aside class="ed-modules admin-editor-scroll" :class="{ 'is-open': modulesOpen }" aria-label="Partes de la invitación">
    <div class="ed-modules__head">
        <div class="min-w-0">
            <h2 class="ed-modules__title">Partes de tu invitación</h2>
            <p class="ed-modules__intro">Enciende lo que quieras mostrar. Después tócalo en la invitación para cambiarlo.</p>
        </div>
        <button type="button" class="ed-modules__close" @click="modulesOpen = false">Listo</button>
    </div>

    <template x-for="group in partGroups" :key="group.id">
        <section class="ed-modules__group">
            <h3 class="ed-modules__label" x-text="group.label"></h3>
            <template x-for="tab in group.tabs" :key="tab.id">
                <div class="ed-part" :class="{ 'is-on': isTabEnabled(tab), 'is-locked': !!packageLockLabel(tab), 'is-pending': isTabEnabled(tab) && examplesOf(tab) > 0 }">
                    <div class="ed-part__row">
                        <button type="button" class="ed-part__name" @click="goToPart(tab)"
                            :title="isTabEnabled(tab) ? 'Ver ' + tab.label + ' en la invitación' : 'Encender ' + tab.label">
                            <span class="ed-part__label" x-text="tab.label"></span>
                            <span class="ed-part__status" x-text="partStatus(tab)"></span>
                        </button>
                        <button type="button" class="ed-help-btn" @click="helpOpen = helpOpen === 'part:' + tab.id ? null : 'part:' + tab.id"
                            :aria-expanded="(helpOpen === 'part:' + tab.id).toString()"
                            :aria-label="'¿Para qué sirve ' + tab.label + '?'">?</button>
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
