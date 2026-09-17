<div class="flex h-full w-full shrink-0 flex-col border-r border-site-line bg-site-bg lg:w-[620px] xl:w-[680px]">
    <div class="shrink-0 border-b border-site-line px-5 py-4">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-xs text-site-muted" x-text="config.isCreate ? 'Nueva invitación' : 'Editando invitación'"></p>
                <h1 class="truncate text-xl font-semibold tracking-tight" x-text="meta.title || 'Invitación sin título'"></h1>
            </div>
            <span class="admin-status-badge shrink-0"
                :class="{ 'is-active': meta.status === 'active' }">
                <span class="admin-status-dot"></span>
                <span x-text="statusLabels[meta.status] ?? meta.status"></span>
            </span>
        </div>
        <p class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-site-muted">
            <span class="inline-flex items-center gap-1.5">
                <x-phosphor-squares-four class="size-4" aria-hidden="true" />
                <span x-text="`${activeModulesCount} módulos activos`"></span>
            </span>
            <span x-text="activeGroupData.description"></span>
        </p>
    </div>

    <div class="flex min-h-0 flex-1">
        <aside class="flex w-44 shrink-0 flex-col border-r border-site-line xl:w-52">
            <nav class="shrink-0 space-y-0.5 border-b border-site-line p-2" aria-label="Apartados del editor">
                <template x-for="group in tabGroups" :key="group.id">
                    <button type="button" @click="selectGroup(group.id)"
                        class="admin-editor-group-btn"
                        :class="activeGroup === group.id ? 'is-active' : ''"
                        :aria-current="activeGroup === group.id ? 'true' : null">
                        <span class="truncate" x-text="group.label"></span>
                        <span class="flex shrink-0 items-center gap-1">
                            <span class="admin-editor-warn" x-show="groupIssueCount(group.id) > 0" x-cloak
                                :title="groupIssueCount(group.id) === 1 ? 'Falta 1 dato' : `Faltan ${groupIssueCount(group.id)} datos`"
                                aria-hidden="true"></span>
                            <span class="admin-editor-group-count"
                                x-show="group.id !== 'config'"
                                x-text="`${groupActiveCount(group.id)}/${group.tabs.length}`"></span>
                        </span>
                    </button>
                </template>
            </nav>

            <div class="admin-editor-scroll flex-1 overflow-y-auto p-2">
                <p class="px-2 pb-2 pt-1 text-xs font-medium text-site-muted" x-text="activeGroupData.label"></p>
                <div class="space-y-0.5">
                    <template x-for="tab in activeGroupTabs" :key="tab.id">
                        <div class="admin-editor-feature-row" :class="activeTab === tab.id ? 'is-active' : ''">
                            <button type="button" @click="selectTab(tab.id)" class="admin-editor-feature-btn"
                                :aria-current="activeTab === tab.id ? 'true' : null">
                                <span class="flex items-center gap-1.5">
                                    <span class="admin-editor-warn" x-show="tabIssues(tab).length > 0" x-cloak aria-hidden="true"></span>
                                    <span class="truncate text-sm font-medium" x-text="tab.label"></span>
                                </span>
                                <span class="mt-0.5 block truncate text-xs"
                                    :class="tabIssues(tab).length > 0 ? 'text-site-danger' : 'text-site-muted'"
                                    x-text="tabIssues(tab).length > 0 ? tabIssues(tab)[0] : tab.hint"></span>
                            </button>
                            <template x-if="tab.moduleCode">
                                <button type="button" role="switch"
                                    @click="toggleModuleForTab(tab)"
                                    class="adm-switch"
                                    :class="isTabEnabled(tab) ? 'is-on' : ''"
                                    :aria-checked="isTabEnabled(tab).toString()"
                                    :aria-label="`Mostrar ${tab.label} en la invitación`"
                                    :title="isTabEnabled(tab) ? 'Visible en la invitación' : 'Oculto en la invitación'"></button>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </aside>

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
                @include('admin.invitations.panels.playlist')
                @include('admin.invitations.panels.hashtag')
                @include('admin.invitations.panels.encuestas')
                @include('admin.invitations.panels.regalos')
                @include('admin.invitations.panels.rsvp')
                @include('admin.invitations.panels.countdown')
                @include('admin.invitations.panels.agendar')
                @include('admin.invitations.panels.fotomural')
                @include('admin.invitations.panels.post-evento')
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
