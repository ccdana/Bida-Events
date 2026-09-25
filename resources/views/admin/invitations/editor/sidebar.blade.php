{{--
    Columna de edición. Arriba: el nombre, el estado y cuánto falta. A la izquierda: todas las secciones
    en una sola lista agrupada (lo básico, la portada, el contenido…), cada una con su estado (lista, le
    falta algo u oculta) y su interruptor. Al centro: la sección abierta, sin tarjetas, con «Anterior» y
    «Siguiente» al pie para recorrerla en orden. En el celular la lista se abre desde «Secciones» y la
    vista previa desde su botón. Estilos: admin.css («Editor»).
--}}
<div class="ed-col flex h-full w-full shrink-0 flex-col border-r border-site-line bg-site-bg lg:w-[680px] xl:w-[760px]">
    <div class="ed-head">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-xs text-site-muted" x-text="config.isCreate ? 'Nueva invitación' : 'Editando invitación'"></p>
                <h1 class="truncate text-xl font-semibold tracking-tight" x-text="meta.title || 'Invitación sin título'"></h1>
            </div>
            <span class="admin-status-badge shrink-0" :class="{ 'is-active': meta.status === 'active' }">
                <span class="admin-status-dot"></span>
                <span x-text="statusLabels[meta.status] ?? meta.status"></span>
            </span>
        </div>

        {{-- Progreso: cuántas secciones encendidas ya tienen lo que necesitan --}}
        <div class="ed-progress" :aria-label="`${progress.done} de ${progress.total} secciones listas`">
            <span class="ed-progress__bar"><i :style="`width: ${progress.total ? Math.round(progress.done / progress.total * 100) : 0}%`"></i></span>
            <span class="ed-progress__text" x-text="progress.done === progress.total ? 'Todo listo para publicar' : `${progress.done} de ${progress.total} secciones listas`"></span>
        </div>

        {{-- Celular: la lista de secciones y la vista previa, cada una a un toque --}}
        <div class="ed-mobile-bar">
            <button type="button" class="admin-link-button" @click="railOpen = true">
                <x-phosphor-list aria-hidden="true" />
                <span x-text="currentTab?.label ?? 'Secciones'"></span>
            </button>
            <button type="button" class="admin-link-button" @click="previewOpen = true">
                <x-phosphor-device-mobile aria-hidden="true" />
                Vista previa
            </button>
        </div>
    </div>

    <div class="relative flex min-h-0 flex-1">
        <aside class="ed-rail admin-editor-scroll" :class="{ 'is-open': railOpen }" aria-label="Secciones de la invitación">
            <div class="ed-rail__close">
                <p class="font-semibold">Secciones</p>
                <button type="button" class="admin-icon-button" @click="railOpen = false" aria-label="Cerrar la lista de secciones">
                    <x-phosphor-x aria-hidden="true" />
                </button>
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

        <form id="invitation-form" method="POST" action="{{ $formAction }}" class="admin-editor-scroll flex flex-1 flex-col overflow-y-auto" @submit.prevent="handleFormSubmit($event)">
            @csrf
            @if($formMethod !== 'POST') @method($formMethod) @endif

            {{-- Datos principales enlazados a Alpine --}}
            <input type="hidden" name="title" :value="meta.title">
            <input type="hidden" name="slug" :value="meta.slug">
            <input type="hidden" name="template" :value="meta.template">
            <input type="hidden" name="event_type_id" :value="meta.event_type_id">
            <input type="hidden" name="user_id" :value="meta.user_id">
            <input type="hidden" name="event_date" :value="meta.event_date">
            <input type="hidden" name="expires_at" :value="meta.expires_at">
            <input type="hidden" name="status" :value="meta.status">
            @if(($editorMode ?? 'admin') === 'admin')
                <input type="hidden" name="package" :value="meta.package">
            @endif

            <div class="flex-1 space-y-4 p-4">
                @include('admin.invitations.panels.general')
                @include('admin.invitations.panels.hero')
                @include('admin.invitations.panels.estetica')
                @include('admin.invitations.panels.ubicacion')
                @include('admin.invitations.panels.itinerario')
                @include('admin.invitations.panels.dress-code')
                @include('admin.invitations.panels.destacados')
                @include('admin.invitations.panels.galeria')
                @include('admin.invitations.panels.video')
                @include('admin.invitations.panels.musica')
                @include('admin.invitations.panels.rsvp-whatsapp')
                @include('admin.invitations.panels.playlist')
                @include('admin.invitations.panels.hashtag')
                @include('admin.invitations.panels.encuestas')
                @include('admin.invitations.panels.regalos')
                @include('admin.invitations.panels.rsvp')
                @include('admin.invitations.panels.countdown')
                @include('admin.invitations.panels.agendar')
                @include('admin.invitations.panels.fotomural')
                @include('admin.invitations.panels.post-evento')

                @include('admin.invitations.panels.textos')

                {{-- Módulos con panel propio (tarjetas y los que se sumen): app/Modules --}}
                @foreach(app(\App\Modules\ModuleRegistry::class)->all() as $registeredModule)
                    @if($registeredModule->panel())
                        @include($registeredModule->panel())
                    @endif
                @endforeach

                {{-- Recorrido en orden: la sección anterior y la siguiente --}}
                <nav class="ed-pager" aria-label="Recorrer las secciones">
                    <button type="button" class="ed-pager__btn" x-show="prevTab" x-cloak @click="selectTab(prevTab.id)">
                        <x-phosphor-arrow-left aria-hidden="true" />
                        <span><small>Anterior</small><span x-text="prevTab?.label"></span></span>
                    </button>
                    <button type="button" class="ed-pager__btn ed-pager__btn--next" x-show="nextTab" x-cloak @click="selectTab(nextTab.id)">
                        <span><small>Siguiente</small><span x-text="nextTab?.label"></span></span>
                        <x-phosphor-arrow-right aria-hidden="true" />
                    </button>
                </nav>
            </div>

            @foreach($moduleCodes as $code)
                <input type="hidden" name="modulos[{{ $code }}]" :value="moduleToJson('{{ $code }}')">
            @endforeach

            {{-- Barra fija: lo que falta, guardar y publicar --}}
            <div class="sticky bottom-0 shrink-0 border-t border-site-line bg-site-bg px-4 py-3">
                {{-- Resumen de lo incompleto, con atajo al apartado donde se arregla --}}
                <details class="admin-editor-pending" x-show="pendingIssues.length > 0" x-cloak>
                    <summary>
                        <x-phosphor-warning-circle class="size-4 shrink-0" aria-hidden="true" />
                        <span x-text="pendingIssues.length === 1
                            ? 'Falta 1 dato para que la invitación se vea completa'
                            : `Faltan ${pendingIssues.length} datos para que la invitación se vea completa`"></span>
                    </summary>
                    <ul>
                        <template x-for="item in pendingIssues" :key="item.tabId + item.issue">
                            <li>
                                <button type="button" @click="selectTab(item.tabId)">
                                    <span class="font-medium" x-text="item.label"></span>
                                    <span class="text-site-muted" x-text="item.issue"></span>
                                </button>
                            </li>
                        </template>
                    </ul>
                </details>

                <div class="flex items-center gap-2">
                    <button type="submit" x-ref="saveButton" :disabled="mediaUploading"
                        class="site-btn site-btn--lg flex-1 justify-center"
                        :class="{ 'is-loading': mediaUploading }">
                        <x-phosphor-floppy-disk x-show="!mediaUploading" aria-hidden="true" />
                        <span x-text="mediaUploading ? 'Subiendo archivos' : 'Guardar invitación'">Guardar invitación</span>
                    </button>

                    <button type="button" x-show="!isPublished" :disabled="mediaUploading"
                        @click="publish()"
                        class="site-btn site-btn--lg shrink-0 justify-center"
                        :title="pendingIssues.length > 0 ? 'Puedes publicar igual: los módulos vacíos no se muestran' : 'La invitación queda visible para los invitados'">
                        <x-phosphor-paper-plane-tilt aria-hidden="true" />
                        Publicar
                    </button>

                    <button type="button" x-show="isPublished" x-cloak :disabled="mediaUploading"
                        @click="unpublish()"
                        class="admin-link-button shrink-0"
                        title="Deja de estar visible para los invitados">
                        <x-phosphor-eye-slash aria-hidden="true" />
                        Despublicar
                    </button>
                </div>

                <p class="mt-2 flex items-center justify-between gap-3 text-xs">
                    <span :class="isPublished ? 'text-site-accent' : 'text-site-muted'">
                        <span x-text="isPublished ? 'Publicada: los invitados ya pueden verla' : 'Sin publicar: solo la ves tú'"></span>
                    </span>
                    <span class="shrink-0 text-site-muted" x-text="`${activeModulesCount} módulos`"></span>
                </p>
            </div>
        </form>
    </div>
</div>
