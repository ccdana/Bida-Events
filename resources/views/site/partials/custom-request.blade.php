{{--
    Aviso de lo a medida: sin precios ni paquetes, solo cómo escribirnos. Lo que se arma a pedido se
    conversa por WhatsApp o por correo, fuera de la tienda. Recibe $contactUrl (WhatsApp) y $bida.
--}}
<p class="mt-10 flex max-w-3xl flex-wrap items-center gap-x-5 gap-y-2" data-reveal>
    <span>
        <span class="font-medium text-site-ink">{{ __('¿Quieres algo a medida?') }}</span>
        <span class="text-site-muted">{{ __('Escríbenos por WhatsApp o al correo y lo conversamos.') }}</span>
    </span>
    <a href="{{ $contactUrl }}" target="_blank" rel="noopener" class="site-link-arrow">
        {{ __('Escribir por WhatsApp') }}
        <x-phosphor-arrow-right aria-hidden="true" />
    </a>
    <a href="mailto:{{ $bida['email'] }}" class="site-link-arrow">
        {{ $bida['email'] }}
        <x-phosphor-arrow-right aria-hidden="true" />
    </a>
</p>