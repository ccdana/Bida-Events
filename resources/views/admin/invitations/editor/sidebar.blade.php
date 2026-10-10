{{--
    Lista de secciones (izquierda): el avance y todas las secciones en una sola lista agrupada, cada una
    con su estado (lista, le falta algo u oculta) y su interruptor. Al centro va la vista previa (preview)
    y a la derecha la sección abierta (panel). En el celular la lista se abre como cajón desde «Todas».
    Estilos: admin.css («Editor»).
--}}
<aside class="ed-rail admin-editor-scroll" :class="{ 'is-open': railOpen }" aria-label="Secciones de la invitación">
    <div class="ed-rail__close">
        <p class="font-semibold">Secciones</p>
        <button type="button" class="admin-icon-button" @click="railOpen = false" aria-label="Cerrar la lista de secciones">
            <x-phosphor-x aria-hidden="true" />
        </button>
    </div>

    {{-- Progreso: cuántas secciones encendidas ya tienen lo que necesitan --}}
    <div class="ed-head">
        <div class="flex items-center justify-between gap-2">
            <p class="text-xs text-site-muted" x-text="config.isCreate ? 'Nueva invitación' : 'Editando'"></p>
            <span class="admin-status-badge shrink-0" :class="{ 'is-active': meta.status === 'active' }">
                <span class="admin-status-dot"></span>
                <span x-text="statusLabels[meta.status] ?? meta.status"></span>
            </span>
        </div>
        <div class="ed-progress" :aria-label="`${progress.done} de ${progress.total} secciones listas`">
            <span class="ed-progress__text" x-text="progress.done === progress.total ? 'Todo listo para publicar' : `${progress.done} de ${progress.total} secciones listas`"></span>
            <span class="ed-progress__bar"><i :style="`width: ${progress.total ? Math.round(progress.done / progress.total * 100) : 0}%`"></i></span>
        </div>
    </div>

    <template x-for="group in tabGroups" :key="group.id">
        <div class="ed-rail__group">
            <p class="ed-rail__label" x-text="group.label"></p>
            <template x-for="tab in group.tabs" :key="tab.id">
                <div class="ed-rail__row" :class="{ 'is-active': activeTab === tab.id, 'is-off': !isTabEnabled(tab) }">
                    <button type="button" class="ed-rail__btn" @click="selectTab(tab.id); railOpen = false"
                        :aria-current="activeTab === tab.id ? 'true' : null">
                        <span class="ed-rail__state" :class="`is-${tabStatus(tab)}`" aria-hidden="true"></span>
                        <span class="min-w-0">
                            <span class="block truncate text-sm font-medium" x-text="tab.label"></span>
                            <span class="block truncate text-xs"
                                :class="tabStatus(tab) === 'issue' ? 'text-site-danger' : 'text-site-muted'"
                                x-text="tabStatus(tab) === 'issue' ? tabIssues(tab)[0] : (tabStatus(tab) === 'locked' ? packageLockLabel(tab) : (tabStatus(tab) === 'off' ? 'Oculta en la invitación' : tab.hint))"></span>
                        </span>
                    </button>
                    <template x-if="tab.moduleCode">
                        <button type="button" role="switch"
                            @click="toggleModuleForTab(tab)"
                            :disabled="!!packageLockLabel(tab)"
                            class="adm-switch"
                            :class="isTabEnabled(tab) ? 'is-on' : ''"
                            :aria-checked="isTabEnabled(tab).toString()"
                            :aria-label="`Mostrar ${tab.label} en la invitación`"
                            :title="isTabEnabled(tab) ? 'Visible en la invitación' : 'Oculta en la invitación'"></button>
                    </template>
                </div>
            </template>
        </div>
    </template>
</aside>
<div class="ed-rail__backdrop" x-show="railOpen" x-cloak x-transition.opacity @click="railOpen = false"></div>
