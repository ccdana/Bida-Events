{{--
    El cliente de este evento (solo lo ve el revendedor que armó la invitación): un acceso para que la familia vea sus
    invitados y descargue sus reportes. Uno por evento; si se creó mal, se elimina y se crea otro. Si el evento es del propio
    revendedor, queda a su nombre sin gastar un acceso. En filas con filete, sin tarjeta. Recibe $invitation.
--}}
@php
    $eventClient = $invitation->user;
    $isOwnEvent = $eventClient && (int) $eventClient->id === (int) auth()->id();
    $canCreateClients = App\Support\ResellerSubscription::canCreateClients(auth()->user());
    $clientsLeft = App\Support\ResellerSubscription::clientsLeft(auth()->user());
@endphp

<section class="grid grid-cols-[minmax(0,1fr)] gap-4" aria-labelledby="cliente-del-evento">
    <h2 id="cliente-del-evento" class="sr-only">Cliente de este evento</h2>

    <p class="ev-meta" title="Con su usuario y contraseña ve quién confirmó y descarga sus reportes. No puede editar la invitación.">
        <span><x-phosphor-user-circle-light aria-hidden="true" /> Un acceso para tu cliente: ve quién confirmó y descarga sus reportes. Editar lo haces tú.</span>
    </p>

    @error('client')
        <p class="ev-note is-danger" role="alert"><x-phosphor-warning-circle-light aria-hidden="true" /> <span>{{ $message }}</span></p>
    @enderror

    {{-- Las credenciales recién creadas se ven una sola vez --}}
    @if(session('client_credentials'))
        @php($credentials = session('client_credentials'))
        <div class="eg-credentials" role="status">
            <p class="ev-label"><x-phosphor-key-light aria-hidden="true" /> Datos de acceso de {{ $credentials['name'] }}</p>
            <dl class="adm-kv">
                <div><dt>Usuario</dt><dd class="font-mono font-semibold">{{ $credentials['username'] }}</dd></div>
                <div><dt>Contraseña</dt><dd class="font-mono font-semibold">{{ $credentials['password'] }}</dd></div>
            </dl>
            <p class="ev-hint"><x-phosphor-info-light aria-hidden="true" /> Cópialos ahora: no se vuelven a mostrar.</p>
            <div>
                <x-ui.copy-button class="site-btn"
                    :text="'Hola '.$credentials['name'].', ya puedes ver quién confirmó en tu invitación. Entra en '.lroute('login').' con el usuario '.$credentials['username'].' y la contraseña '.$credentials['password'].'.'"
                    label="Copiar mensaje para enviar" done="Mensaje copiado" icon="whatsapp-logo-light" plain />
            </div>
        </div>
    @endif

    @if($isOwnEvent)
        <div class="eg-client">
            <p class="flex items-center gap-2">
                <x-phosphor-user-circle-light class="size-5 text-site-accent" aria-hidden="true" />
                <span><span class="font-medium">Este evento es tuyo.</span> <span class="text-sm text-site-muted">Sus invitados y reportes están en esta página.</span></span>
            </p>
            <form method="POST" action="{{ route('client.invitations.client.destroy', $invitation) }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="site-help">
                    <x-phosphor-arrow-u-up-left-light aria-hidden="true" />
                    Es de un cliente
                </button>
            </form>
        </div>
    @elseif($eventClient)
        <div class="eg-client">
            <p>
                <span class="font-medium">{{ $eventClient->name }}</span>
                <span class="ml-1 font-mono text-sm text-site-muted">{{ $eventClient->username }}</span>
            </p>
            @if((int) $eventClient->created_by_reseller_id === (int) auth()->id())
                <form method="POST" action="{{ route('client.invitations.client.destroy', $invitation) }}"
                    onsubmit="return confirm('¿Eliminar el acceso de {{ e($eventClient->name) }}? Ya no podrá entrar; después puedes crear otro.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="adm-del">
                        <x-phosphor-trash-light aria-hidden="true" />
                        Eliminar cliente
                    </button>
                </form>
            @endif
        </div>
    @elseif($canCreateClients)
        <form method="POST" action="{{ route('client.invitations.client.store', $invitation) }}" class="eg-form">
            @csrf
            <label class="eg-field">
                <span class="site-label ed-wiz-label"><x-phosphor-user-plus-light aria-hidden="true" /> Nombre del cliente</span>
                <input type="text" name="name" required maxlength="255" value="{{ old('name') }}" class="site-input" placeholder="Familia Quispe">
                @error('name')<span class="eg-error">{{ $message }}</span>@enderror
            </label>
            <div>
                <button type="submit" class="site-btn"><x-phosphor-user-plus-light aria-hidden="true" /> Crear acceso</button>
            </div>
            @if($clientsLeft !== null)
                <p class="ev-hint" title="Tantos como las invitaciones de tu plan.">
                    <x-phosphor-info-light aria-hidden="true" />
                    {{ $clientsLeft === 1 ? 'Te queda 1 acceso de cliente este mes' : "Te quedan {$clientsLeft} accesos de cliente este mes" }}
                </p>
            @endif
        </form>
    @else
        <p class="ev-note"><x-phosphor-info-light aria-hidden="true" /> <span>Ya creaste todos los accesos de cliente de este mes (tantos como las invitaciones de tu plan). El mes que viene puedes crear más, o pasar a un plan con más invitaciones.</span></p>
    @endif

    {{-- Sin cliente todavía: también puede ser un evento del propio revendedor --}}
    @unless($eventClient)
        <form method="POST" action="{{ route('client.invitations.client.self', $invitation) }}" class="eg-client">
            @csrf
            <p class="text-sm text-site-muted">¿Es un evento tuyo?</p>
            <button type="submit" class="site-help">
                <x-phosphor-user-circle-light aria-hidden="true" />
                Es mío, no necesito un acceso
            </button>
        </form>
    @endunless
</section>
