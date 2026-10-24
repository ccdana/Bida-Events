{{-- Confirmación de la última acción (enlace enviado, contraseña cambiada…) --}}
@if(session('status'))
    <p class="site-enter mt-4 flex items-start gap-2 border-l-2 border-site-accent py-1 pl-3 text-sm" role="status" style="--enter-index: {{ $index ?? 3 }}">
        <x-phosphor-check-circle class="mt-0.5 size-4 shrink-0 text-site-accent" aria-hidden="true" />
        {{ session('status') }}
    </p>
@endif
