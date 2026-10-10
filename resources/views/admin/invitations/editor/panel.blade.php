{{--
    Columna de la derecha: la sección abierta, sin tarjetas, con «Anterior» y «Siguiente» al pie, y la
    barra fija con guardar y publicar. En el celular es una hoja que sube desde abajo, con las secciones
    como fichas. Estilos: admin.css («Editor»).
--}}
<div class="ed-panel" :class="{ 'is-tall': sheetTall }">
    <div class="ed-panel__top">
        <button type="button" class="ed-panel__handle" @click="sheetTall = !sheetTall"
            :aria-label="sheetTall ? 'Bajar el panel para ver más la invitación' : 'Subir el panel para editar con más espacio'"
            :aria-expanded="sheetTall.toString()"></button>
        <div class="ed-chips" role="tablist" aria-label="Secciones">
            <button type="button" class="ed-chip ed-chip--all" @click="railOpen = true">Todas</button>
            <template x-for="tab in flatTabs.filter(item => isTabEnabled(item) || item.id === activeTab)" :key="tab.id">
                <button type="button" role="tab" class="ed-chip" :class="{ 'is-active': activeTab === tab.id, 'is-issue': tabStatus(tab) === 'issue' }"
                    :aria-selected="(activeTab === tab.id).toString()" @click="selectTab(tab.id)" x-text="tab.label"></button>
            </template>
        </div>
    </div>

<form id="invitation-form" method="POST" action="{{ $formAction }}" class="admin-editor-scroll flex min-h-0 flex-1 flex-col overflow-y-auto" @submit.prevent="handleFormSubmit($event)">
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

        <p class="mt-2 hidden items-center justify-between gap-3 text-xs lg:flex">
            <span :class="isPublished ? 'text-site-accent' : 'text-site-muted'">
                <span x-text="isPublished ? 'Publicada: los invitados ya pueden verla' : 'Sin publicar: solo la ves tú'"></span>
            </span>
            <span class="shrink-0 text-site-muted" x-text="`${activeModulesCount} módulos`"></span>
        </p>
    </div>
</form>
</div>
