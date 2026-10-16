{{-- Confirmación de la última acción (enlace enviado, contraseña cambiada…) --}}
@if(session('status'))
    <p class="site-enter mt-8 flex items-start gap-2 rounded-[12px] border border-site-line bg-site-surface px-4 py-3 text-sm leading-relaxed" role="status" style="--enter-index: {{ $index ?? 3 }}">
        <x-phosphor-check-circle class="mt-0.5 size-5 shrink-0 text-site-accent" aria-hidden="true" />
        {{ session('status') }}
    </p>
@endif
