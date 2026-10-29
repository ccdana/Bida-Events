{{--
    Control de entrada del evento: el enlace de puerta que el organizador le pasa a quien recibe a los invitados, y cuántas
    personas ya ingresaron. En filas con filete: el enlace con sus íconos y las dos acciones (uno nuevo, cerrar la puerta).
    Recibe $invitation y $doorStats.
--}}
@php
    $doorUrl = $invitation->door_token ? route('door.open', $invitation->door_token) : null;
    $doorMessage = $doorUrl
        ? "Enlace para controlar la entrada de «{$invitation->title}». Ábrelo en tu teléfono antes del evento y escanea el QR del pase de cada invitado: {$doorUrl}"
        : null;
@endphp

<section class="grid grid-cols-[minmax(0,1fr)] gap-4" aria-labelledby="control-entrada">
    <h2 id="control-entrada" class="sr-only">Control de entrada</h2>

    @if($doorUrl)
        <p class="ev-next__count">
            <span><b>{{ $doorStats['arrived'] }}</b> de {{ $doorStats['expected'] }} personas ingresaron</span>
        </p>

        <div class="ev-note">
            <x-phosphor-qr-code-light aria-hidden="true" />
            <span>Envía este enlace a quien reciba a los invitados: lo abre una vez en su teléfono y escanea el QR del pase de cada uno. Nadie más puede registrar ingresos.</span>
        </div>

        <div class="eg-door">
            <code class="eg-door__url">{{ $doorUrl }}</code>
            <div class="ev-acts">
                <x-ui.copy-button :text="$doorUrl" label="Copiar enlace" done="Enlace copiado" icon="copy-light" plain class="ev-act" title="Copiar enlace" />
                <a href="https://wa.me/?text={{ rawurlencode($doorMessage) }}" target="_blank" rel="noopener" class="ev-act" title="Mandar por WhatsApp" aria-label="Mandar el enlace de la puerta por WhatsApp">
                    <x-phosphor-whatsapp-logo-light aria-hidden="true" />
                </a>
            </div>
        </div>

        <div class="eg-actions">
            <form method="POST" action="{{ route('client.door.store', $invitation) }}"
                onsubmit="return confirm('¿Crear un enlace nuevo? El que ya enviaste dejará de funcionar.')">
                @csrf
                <button type="submit" class="site-help">
                    <x-phosphor-arrows-clockwise-light aria-hidden="true" />
                    Crear un enlace nuevo
                </button>
            </form>
            <form method="POST" action="{{ route('client.door.destroy', $invitation) }}"
                onsubmit="return confirm('¿Cerrar el control de entrada? El enlace de puerta dejará de funcionar.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="adm-del">
                    <x-phosphor-lock-simple-light aria-hidden="true" />
                    Cerrar la puerta
                </button>
            </form>
        </div>
    @else
        <div class="ev-empty">
            <x-phosphor-qr-code-light class="ev-empty__icon" aria-hidden="true" />
            <h3 class="ev-empty__title">El día del evento, escanea el pase de cada invitado</h3>
            <p class="ev-empty__text">Quien reciba a los invitados lo hace desde su teléfono: ve si confirmó, cuántas personas entran y si ese pase ya se usó.</p>
            <form method="POST" action="{{ route('client.door.store', $invitation) }}">
                @csrf
                <button type="submit" class="site-btn site-btn--lg">
                    <x-phosphor-qr-code-light aria-hidden="true" />
                    Crear enlace de puerta
                </button>
            </form>
        </div>
    @endif
</section>
