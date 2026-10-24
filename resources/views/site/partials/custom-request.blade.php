{{--
    Aviso de lo a medida: sin precios ni paquetes, solo cómo escribirnos. Lo que se arma a pedido se
    conversa por WhatsApp o por correo, fuera de la tienda: dos botones redondos con su ícono (el nombre
    va en el aria-label). Recibe $contactUrl (WhatsApp) y $bida y, opcional, $customLabel (la frase que acompaña
    a los íconos; por defecto «¿Quieres algo a medida?»).
--}}
<p class="site-custom" data-reveal>
    <span class="font-medium text-site-ink">{{ $customLabel ?? __('¿Quieres algo a medida?') }}</span>
    <a href="{{ $contactUrl }}" target="_blank" rel="noopener" class="site-icon-btn site-icon-btn--whatsapp" aria-label="{{ __('Escribir por WhatsApp') }}" title="{{ __('Escribir por WhatsApp') }}">
        <x-phosphor-whatsapp-logo aria-hidden="true" />
    </a>
    <a href="mailto:{{ $bida['email'] }}" class="site-icon-btn" aria-label="{{ __('Escribir al correo :email', ['email' => $bida['email']]) }}" title="{{ $bida['email'] }}">
        <x-phosphor-envelope-simple aria-hidden="true" />
    </a>
</p>
