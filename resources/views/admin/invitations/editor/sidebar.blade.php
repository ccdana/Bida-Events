<div class="w-full lg:w-[620px] xl:w-[680px] shrink-0 flex flex-col border-r border-stone-200 bg-stone-50 h-full">
    <div class="shrink-0 border-b border-stone-200/80 bg-white/95 px-4 py-4 backdrop-blur-sm">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-[10px] uppercase tracking-widest text-stone-400" x-text="config.isCreate ? 'Nueva invitación' : 'Editando invitación'"></p>
                <h1 class="truncate text-lg font-serif text-stone-950" x-text="meta.title || 'Invitación sin título'"></h1>
            </div>
            <span class="admin-status-badge shrink-0"
                :class="{
                    'is-active': meta.status === 'active',
                    'is-draft': meta.status === 'draft',
                    'is-pending': meta.status === 'pending',
                    'is-declined': meta.status === 'declined',
                    'is-suspended': meta.status === 'suspended',
                    'is-expired': meta.status === 'expired'
                }">
                <span class="admin-status-dot"></span>
                <span x-text="meta.status"></span>
            </span>
        </div>
        <p class="mt-2 text-xs text-stone-500">
            <span x-text="`${activeModulesCount} módulos activos`"></span>
            <span class="mx-1 text-stone-300">·</span>
            <span x-text="activeGroupData.description"></span>
        </p>
    </div>

    <div class="flex flex-1 min-h-0">
        <aside class="w-44 xl:w-48 shrink-0 flex flex-col border-r border-stone-200 bg-white">
            <div class="shrink-0 px-3 py-3 border-b border-stone-100">
                <p class="text-[10px] uppercase tracking-[0.28em] text-stone-400">Apartados</p>
            </div>
            <nav class="shrink-0 p-2 space-y-1 border-b border-stone-100">
                <template x-for="group in tabGroups" :key="group.id">
                    <button type="button" @click="selectGroup(group.id)"
                        class="admin-editor-group-btn w-full"
                        :class="activeGroup === group.id ? 'is-active' : ''">
                        <span class="truncate" x-text="group.label"></span>
                        <span class="admin-editor-group-count"
                            x-show="group.id !== 'config'"
                            x-text="`${groupActiveCount(group.id)}/${group.tabs.length}`"></span>
                    </button>
                </template>
            </nav>

            <div class="flex-1 overflow-y-auto admin-editor-scroll p-2">
                <p class="px-2 pb-2 text-[10px] uppercase tracking-[0.28em] text-stone-400" x-text="activeGroupData.label"></p>
                <div class="space-y-1">
                    <template x-for="tab in activeGroupTabs" :key="tab.id">
                        <div class="admin-editor-feature-row"
                            :class="activeTab === tab.id ? 'is-active' : ''">
                            <button type="button" @click="selectTab(tab.id)" class="admin-editor-feature-btn">
                                <span class="min-w-0">
                                    <span class="block truncate text-sm font-medium" x-text="tab.label"></span>
                                    <span class="mt-0.5 block truncate text-[11px] text-stone-500" x-text="tab.hint"></span>
                                </span>
                            </button>
                            <template x-if="tab.moduleCode">
                                <button type="button"
                                    @click="toggleModuleForTab(tab)"
                                    class="admin-editor-feature-toggle"
                                    :class="isTabEnabled(tab) ? 'is-on' : 'is-off'"
                                    :title="isTabEnabled(tab) ? 'Desactivar módulo' : 'Activar módulo'">
                                    <span class="admin-editor-feature-toggle-dot"></span>
                                    <span x-text="isTabEnabled(tab) ? 'On' : 'Off'"></span>
                                </button>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </aside>

        <form id="invitation-form" method="POST" action="{{ $formAction }}" class="flex-1 overflow-y-auto admin-editor-scroll flex flex-col" @submit.prevent="handleFormSubmit($event)">
            @csrf
            @if($formMethod !== 'POST') @method($formMethod) @endif

            {{-- Hidden meta inputs bound to Alpine --}}
            <input type="hidden" name="title" :value="meta.title">
            <input type="hidden" name="slug" :value="meta.slug">
            <input type="hidden" name="template" :value="meta.template">
            <input type="hidden" name="event_type_id" :value="meta.event_type_id">
            <input type="hidden" name="user_id" :value="meta.user_id">
            <input type="hidden" name="event_date" :value="meta.event_date">
            <input type="hidden" name="expires_at" :value="meta.expires_at">
            <input type="hidden" name="status" :value="meta.status">

            <div class="flex-1 p-4 space-y-4 pb-4">
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

            {{-- Sticky Save Button --}}
            <div class="shrink-0 border-t border-stone-200 bg-white/95 backdrop-blur-sm px-4 py-3">
                <div class="flex items-center gap-3">
                    <button type="submit" :disabled="mediaUploading"
                        class="flex-1 flex items-center justify-center gap-2 rounded-xl py-2.5 text-sm font-semibold transition"
                        :class="mediaUploading
                            ? 'bg-stone-100 text-stone-400 cursor-not-allowed'
                            : 'bg-stone-900 text-white hover:bg-stone-800 active:scale-[0.98] shadow-md hover:shadow-lg'">
                        <template x-if="!mediaUploading">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                            </svg>
                        </template>
                        <template x-if="mediaUploading">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                            </svg>
                        </template>
                        <span x-text="mediaUploading ? 'Guardando...' : 'Guardar invitación'"></span>
                    </button>
                    <div class="shrink-0 text-right">
                        <p class="text-[10px] uppercase tracking-wider font-semibold"
                            :class="{
                                'text-emerald-600': meta.status === 'active',
                                'text-stone-400': meta.status === 'draft',
                                'text-amber-600': meta.status === 'suspended',
                                'text-red-500': meta.status === 'expired'
                            }"
                            x-text="{active:'Activa',draft:'Borrador',suspended:'Suspendida',expired:'Expirada'}[meta.status] ?? meta.status">
                        </p>
                        <p class="text-[10px] text-stone-400" x-text="`${activeModulesCount} módulos`"></p>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
