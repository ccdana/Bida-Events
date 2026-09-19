{{-- Menú de secciones: panel a pantalla completa con la sección activa resaltada --}}
@php($navItems = $page->navItems())
<div x-data="invitationNav(@js(array_column($navItems, 'id')))"
    @keydown.escape.window="close()"
    @inv-story-change.window="active = $event.detail.id || active"
    @keydown.tab="trapFocus($event)"
    x-effect="document.documentElement.classList.toggle('inv-lock', open)">
    <button type="button"
        class="inv-nav__toggle"
        x-ref="toggle"
        :class="{ 'is-open': open }"
        @click="toggle()"
        :aria-expanded="open.toString()"
        aria-controls="inv-nav-panel">
        <span x-text="open ? 'Cerrar' : 'Menú'">Menú</span>
        <span class="inv-nav__bars" aria-hidden="true"><span></span><span></span></span>
    </button>

    <div class="inv-nav__backdrop" x-show="open" x-cloak x-transition.opacity @click="close()"></div>

    <nav id="inv-nav-panel"
        class="inv-nav__panel"
        x-show="open" x-cloak
        x-ref="panel"
        :aria-hidden="open ? null : 'true'"
        x-transition:enter="inv-nav-anim"
        x-transition:enter-start="inv-nav-hidden"
        x-transition:enter-end="inv-nav-shown"
        x-transition:leave="inv-nav-anim"
        x-transition:leave-start="inv-nav-shown"
        x-transition:leave-end="inv-nav-hidden"
        aria-label="Secciones de la invitación">
        <p class="inv-label">{{ $invCopy['menu_heading'] ?? 'Invitación de' }}</p>
        <p class="inv-nav__heading">{{ $page->displayName }}</p>

        <ol class="inv-nav__list">
            @foreach($navItems as $index => $item)
                <li>
                    <a href="#{{ $item['id'] }}"
                        class="inv-nav__link"
                        :class="{ 'is-active': active === '{{ $item['id'] }}' }"
                        @click="close(false)">
                        <span class="inv-nav__num">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                        {{ $item['label'] }}
                    </a>
                </li>
            @endforeach
        </ol>
    </nav>
</div>
