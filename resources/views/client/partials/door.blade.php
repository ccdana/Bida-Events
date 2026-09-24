{{--
    Control de entrada del evento: el enlace de puerta que el organizador le pasa a quien recibe a
    los invitados, y cuántas personas ya ingresaron. Recibe $invitation y $doorStats.
--}}
@php
    $doorUrl = $invitation->door_token ? route('door.open', $invitation->door_token) : null;
    $doorMessage = $doorUrl
        ? "Enlace para controlar la entrada de «{$invitation->title}». Ábrelo en tu teléfono antes del evento y escanea el QR del pase de cada invitado: {$doorUrl}"
        : null;
@endphp

<section class="site-enter mt-10" style="--enter-index: 1" aria-labelledby="control-entrada">
    <div class="flex flex-wrap items-end justify-between gap-3 border-b border-site-line pb-3">
        <h2 id="control-entrada" class="text-xl font-semibold tracking-tight">Control de entrada</h2>
        @if($doorUrl)
            <p class="text-sm text-site-muted">
                <span class="font-semibold text-site-ink">{{ $doorStats['arrived'] }}</span> de {{ $doorStats['expected'] }} personas ingresaron
            </p>
        @endif
    </div>

    @if($doorUrl)
        <p class="mt-4 max-w-[62ch] text-sm leading-relaxed text-site-muted">
            Envía este enlace a quien reciba a los invitados. Lo abre una vez en su teléfono y después escanea el QR del
            pase de cada invitado (con la cámara del teléfono o desde la misma página): verá si puede pasar y cuántas
            personas entran. Nadie más puede registrar ingresos.
        </p>

        <div class="mt-4 flex flex-wrap items-center gap-2">
            <code class="max-w-full truncate rounded-[10px] bg-site-tint px-3 py-2 font-mono text-xs">{{ $doorUrl }}</code>
            <x-ui.copy-button class="admin-link-button" :text="$doorUrl" label="Copiar enlace" done="Enlace copiado" icon="copy" />
            <a href="https://wa.me/?text={{ rawurlencode($doorMessage) }}" target="_blank" rel="noopener" class="admin-link-button">
                <x-phosphor-whatsapp-logo aria-hidden="true" />
                Enviar por WhatsApp
            </a>
        </div>

        <div class="mt-4 flex flex-wrap gap-4 text-sm">
            <form method="POST" action="{{ route('client.door.store', $invitation) }}"
                onsubmit="return confirm('¿Crear un enlace nuevo? El que ya enviaste dejará de funcionar.')">
                @csrf
                <button type="submit" class="admin-link-button">
                    <x-phosphor-arrows-clockwise aria-hidden="true" />
                    Crear un enlace nuevo
                </button>
            </form>
            <form method="POST" action="{{ route('client.door.destroy', $invitation) }}"
                onsubmit="return confirm('¿Cerrar el control de entrada? El enlace de puerta dejará de funcionar.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="admin-link-button is-danger">
                    <x-phosphor-lock-simple aria-hidden="true" />
                    Cerrar la puerta
                </button>
            </form>
        </div>
    @else
        <div class="mt-4 flex flex-wrap items-center justify-between gap-4">
            <p class="max-w-[58ch] text-sm leading-relaxed text-site-muted">
                El día del evento, quien reciba a los invitados puede escanear el QR del pase de cada uno desde su teléfono:
                ve si confirmó, cuántas personas entran y si ese pase ya se usó.
            </p>
            <form method="POST" action="{{ route('client.door.store', $invitation) }}">
                @csrf
                <button type="submit" class="admin-primary-button min-h-11">
                    <x-phosphor-qr-code aria-hidden="true" />
                    Crear enlace de puerta
                </button>
            </form>
        </div>
    @endif
</section>
