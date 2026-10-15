{{--
    Lo que se abre al tocar algo en la invitación («tocar y cambiar», como en Canva):
    - Un texto o una foto ('field'): una burbuja junto a él, con solo ese campo, su «?» y «Listo».
    - El botón «Cambiar …» de una parte ('section'): la burbuja con una fila por cosa que se puede cambiar.
    - «Datos del evento», «Colores y letras» y «Ver todas las opciones» ('full'): panel lateral con el
      formulario entero de esa parte.
    En el celular la burbuja y el panel son hojas que suben desde abajo. El formulario siempre está en la
    página (aunque no se vea): el botón Guardar de la cabecera y Publicar lo envían.
    Lógica en resources/js/admin/editor.js (focusContainer, placeBubble); estilos en admin.css («Editor: estilo Canva»).
--}}
<div class="ed-panel" x-ref="panel" x-show="isEditingPart" x-cloak
    :class="{ 'is-tall': sheetTall, 'is-field': panelMode === 'field', 'is-section': panelMode === 'section', 'is-full': panelMode === 'full', 'is-bubble': isBubble, 'is-placed': bubble.ready, ['side-' + bubble.side]: isBubble }"
    :style="bubbleStyle"
    role="dialog" :aria-label="panelMode === 'field' ? 'Cambiar ' + fieldLabel : (currentTab?.label ?? 'Editar')"
    @keydown.escape.window="isEditingPart && !cropperOpen && finishEditing()">
    <span class="ed-panel__arrow" aria-hidden="true"></span>

    <div class="ed-panel__top">
        <button type="button" class="ed-panel__handle" @click="sheetTall = !sheetTall"
            :aria-label="sheetTall ? 'Bajar el panel para ver más la invitación' : 'Subir el panel para editar con más espacio'"
            :aria-expanded="sheetTall.toString()"></button>

        <div class="ed-panel__head">
            <button type="button" class="ed-panel__back" x-show="panelMode !== 'section' && !!currentTab?.moduleCode" @click="goBack()"
                :aria-label="'Ver todo lo que se puede cambiar de ' + (currentTab?.label ?? 'esta parte')"
                :title="'Más de ' + (currentTab?.label ?? 'esta parte')">
                <x-phosphor-caret-left aria-hidden="true" />
            </button>
            <p class="ed-panel__title">
                <small x-text="panelMode === 'field' ? (currentTab?.label ?? '') : (panelMode === 'section' ? '¿Qué quieres cambiar?' : (currentTab?.moduleCode ? 'Todas las opciones' : 'Tu invitación'))"></small>
                <span x-text="panelMode === 'field' ? (fieldLabel || currentTab?.label) : (currentTab?.label ?? 'La invitación')"></span>
            </p>
            <button type="button" class="ed-help-btn" @click="helpOpen = helpOpen === 'panel' ? null : 'panel'"
                :aria-expanded="(helpOpen === 'panel').toString()" aria-label="¿Para qué sirve?" title="¿Para qué sirve?">?</button>
            <button type="button" class="ed-panel__done" @click="finishEditing()">Listo</button>
        </div>

        <p class="ed-panel__help" x-show="helpOpen === 'panel'" x-cloak x-transition.opacity x-text="currentHelp"></p>
        <p class="ed-panel__notice" x-show="partNotice" x-cloak x-text="partNotice"></p>

        {{-- Mostrar u ocultar la parte entera --}}
        <div class="ed-panel__show" x-show="(panelMode === 'section' || panelMode === 'full') && !!currentTab?.moduleCode" x-cloak>
            <span x-text="currentTab ? (packageLockLabel(currentTab) || (isTabEnabled(currentTab) ? 'Se muestra en la invitación' : 'Oculta: tus invitados no la ven')) : ''"></span>
            <button type="button" role="switch" class="adm-switch"
                :class="currentTab && isTabEnabled(currentTab) ? 'is-on' : ''"
                :disabled="!currentTab || !!packageLockLabel(currentTab)"
                :aria-checked="(!!currentTab && isTabEnabled(currentTab)).toString()"
                :aria-label="'Mostrar ' + (currentTab?.label ?? '') + ' en la invitación'"
                @click="currentTab && togglePart(currentTab)"></button>
        </div>
    </div>

    {{-- Las opciones de la parte: una fila por cosa que se puede cambiar --}}
    <div class="ed-choices admin-editor-scroll" x-show="panelMode === 'section'" x-cloak>
        <template x-for="choice in sectionChoices" :key="choice.id">
            <button type="button" class="ed-choice" @click="openChoice(choice)">
                <span class="ed-choice__thumb" :class="{ 'has-image': !!choice.image }" aria-hidden="true">
                    <img x-show="choice.image" :src="choice.image || ''" alt="">
                    <x-phosphor-pencil-simple x-show="!choice.image" />
                </span>
                <span class="min-w-0 flex-1">
                    <span class="ed-choice__label" x-text="choice.label"></span>
                    <span class="ed-choice__value" x-show="choice.summary" x-text="choice.summary"></span>
                </span>
                <x-phosphor-caret-right class="ed-choice__go" aria-hidden="true" />
            </button>
        </template>
        <button type="button" class="ed-choices__all" @click="openFull()">
            Ver todas las opciones de <span x-text="currentTab?.label ?? 'esta parte'"></span>
        </button>
    </div>

    {{-- Un campo a la vista: el resto de la parte, a un toque --}}
    <button type="button" class="ed-field-more" x-show="panelMode === 'field' && !!currentTab?.moduleCode" x-cloak @click="openSection(activeTab)">
        <span>Cambiar otra cosa de <span x-text="currentTab?.label ?? 'esta parte'"></span></span>
        <x-phosphor-caret-right aria-hidden="true" />
    </button>

{{-- novalidate: un campo obligatorio de una parte cerrada no puede frenar el guardado sin que se vea; valida el servidor --}}
<form id="invitation-form" method="POST" action="{{ $formAction }}" novalidate class="admin-editor-scroll flex min-h-0 flex-1 flex-col overflow-y-auto" @submit.prevent="handleFormSubmit($event)">
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
    {{-- Una invitación nueva: si la dirección ya existe, el servidor le suma un número --}}
    <input type="hidden" name="slug_auto" :value="config.isCreate ? 1 : 0">
    @if(($editorMode ?? 'admin') === 'admin')
        <input type="hidden" name="package" :value="meta.package">
    @endif
    {{-- «Hazlo tú»: después de guardar, a la página de publicar (Client\DiyInvitationController) --}}
    @if(($editorMode ?? 'admin') === 'diy')
        <input type="hidden" name="next" :value="diyNext">
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

        {{-- Recorrido en orden: la parte anterior y la siguiente --}}
        <nav class="ed-pager" aria-label="Recorrer las partes de la invitación">
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
</form>
</div>
