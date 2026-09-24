{{--
    El cliente de este evento (solo lo ve el revendedor que armó la invitación): un acceso para que
    la familia vea sus invitados y descargue sus reportes. Uno por evento; si se creó mal, se elimina
    y se crea otro. Recibe $invitation.
--}}
@php
    $eventClient = $invitation->user;
    $canCreateClients = App\Support\ResellerSubscription::canCreateClients(auth()->user());
    $clientsLeft = App\Support\ResellerSubscription::clientsLeft(auth()->user());
@endphp

<section class="site-enter mt-10 rounded-[16px] border border-site-line bg-site-surface p-5" style="--enter-index: 1">
    <h2 class="text-lg font-semibold tracking-tight">Cliente de este evento</h2>
    <p class="mt-1 max-w-[60ch] text-sm text-site-muted">
        Un acceso para tu cliente: con su usuario y contraseña ve quién confirmó y descarga sus reportes.
        No puede editar la invitación; eso lo haces tú.
    </p>

    @error('client')
        <p class="mt-3 text-sm text-site-danger">{{ $message }}</p>
    @enderror

    {{-- Las credenciales recién creadas se ven una sola vez --}}
    @if(session('client_credentials'))
        @php($credentials = session('client_credentials'))
        <div class="mt-4 rounded-[12px] bg-site-tint p-4" role="status">
            <p class="text-sm font-medium">Datos de acceso de {{ $credentials['name'] }} (cópialos ahora: no se vuelven a mostrar)</p>
            <dl class="mt-2 grid gap-2 text-sm sm:grid-cols-2">
                <div>
                    <dt class="text-site-muted">Usuario</dt>
                    <dd class="font-mono font-semibold">{{ $credentials['username'] }}</dd>
                </div>
                <div>
                    <dt class="text-site-muted">Contraseña</dt>
                    <dd class="font-mono font-semibold">{{ $credentials['password'] }}</dd>
                </div>
            </dl>
            <x-ui.copy-button class="admin-link-button mt-3"
                :text="'Hola '.$credentials['name'].', ya puedes ver quién confirmó en tu invitación. Entra en '.route('login').' con el usuario '.$credentials['username'].' y la contraseña '.$credentials['password'].'.'"
                label="Copiar mensaje para enviar" done="Mensaje copiado" icon="whatsapp-logo" />
        </div>
    @endif

    @if($eventClient)
        <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
            <p>
                <span class="font-medium">{{ $eventClient->name }}</span>
                <span class="ml-1 font-mono text-sm text-site-muted">{{ $eventClient->username }}</span>
            </p>
            @if((int) $eventClient->created_by_reseller_id === (int) auth()->id())
                <form method="POST" action="{{ route('client.invitations.client.destroy', $invitation) }}"
                    onsubmit="return confirm('¿Eliminar el acceso de {{ e($eventClient->name) }}? Ya no podrá entrar; después puedes crear otro.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="admin-link-button is-danger">
                        <x-phosphor-trash aria-hidden="true" />
                        Eliminar cliente
                    </button>
                </form>
            @endif
        </div>
    @elseif($canCreateClients)
        <form method="POST" action="{{ route('client.invitations.client.store', $invitation) }}" class="mt-4 flex flex-wrap items-end gap-3">
            @csrf
            <div class="min-w-[14rem] flex-1">
                <label for="cliente-evento" class="admin-label">Nombre del cliente</label>
                <input id="cliente-evento" type="text" name="name" required maxlength="255" value="{{ old('name') }}"
                    class="admin-input" placeholder="Familia Quispe">
                @error('name')
                    <p class="mt-1.5 text-sm text-site-danger">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="admin-primary-button min-h-11">
                <x-phosphor-user-plus aria-hidden="true" />
                Crear acceso
            </button>
        </form>
        @if($clientsLeft !== null)
            <p class="mt-2 text-xs text-site-muted">
                {{ $clientsLeft === 1 ? 'Te queda 1 acceso de cliente este mes' : "Te quedan {$clientsLeft} accesos de cliente este mes" }}
                (tantos como las invitaciones de tu plan).
            </p>
        @endif
    @else
        <p class="mt-4 text-sm text-site-muted">Ya creaste todos los accesos de cliente de este mes (tantos como las invitaciones de tu plan). El mes que viene puedes crear más, o pasar a un plan con más invitaciones.</p>
    @endif
</section>
