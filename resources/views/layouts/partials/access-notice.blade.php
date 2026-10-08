{{-- Aviso cuando no se tiene acceso a una sección (reemplaza la página de error 403) --}}
@if(session('access_notice'))
    <p class="site-enter mb-6 flex items-start gap-2 rounded-[12px] border border-site-line bg-site-tint px-4 py-3 text-sm" role="alert">
        <x-phosphor-lock-simple class="mt-0.5 size-5 shrink-0 text-site-accent" aria-hidden="true" />
        {{ session('access_notice') }}
    </p>
@endif
