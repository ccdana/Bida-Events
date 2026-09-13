{{-- Cambiar tema y cerrar sesión, al final de la cabecera de cada panel --}}
@include('layouts.partials.theme-toggle')
<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit" class="admin-icon-button" aria-label="Cerrar sesión" title="Cerrar sesión">
        <x-phosphor-sign-out aria-hidden="true" />
    </button>
</form>
