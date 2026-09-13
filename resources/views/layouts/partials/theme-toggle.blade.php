{{-- Botón de tema claro/oscuro (requiere layouts.partials.theme-script en el <head>) --}}
<button type="button" onclick="toggleTheme()" class="theme-toggle admin-icon-button {{ $class ?? '' }}"
    aria-label="Cambiar entre tema claro y oscuro" title="Cambiar tema">
    <x-phosphor-moon class="theme-toggle__icon theme-toggle__icon--moon" aria-hidden="true" />
    <x-phosphor-sun class="theme-toggle__icon theme-toggle__icon--sun" aria-hidden="true" />
</button>
