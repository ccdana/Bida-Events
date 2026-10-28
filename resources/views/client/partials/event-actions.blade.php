{{--
    Las acciones de un evento, solo con íconos (cada una con su nombre en el título): invitados o respuestas, editar,
    abrir, copiar el enlace, mandarlo por WhatsApp y, en las que armó el equipo, pedir un cambio. Recibe $row (ver
    App\ViewModels\Client\DashboardViewData) y, opcional, $primary (la acción principal con palabra, para el destacado).
--}}
@php($invitation = $row['invitation'])
@php($primary ??= false)

<div class="ev-acts">
    {{-- El panel de invitados viene en el paquete Premium (App\Support\Packages) --}}
    @can('view', $invitation)
        <a href="{{ route('client.invitation.show', $invitation) }}" @class(['site-btn' => $primary, 'ev-act' => ! $primary])
            title="{{ $row['isCard'] ? 'Ver respuestas' : 'Ver invitados' }}" aria-label="{{ $row['isCard'] ? 'Ver respuestas' : 'Ver invitados' }}">
            <x-dynamic-component :component="$row['isCard'] ? 'phosphor-chat-circle-text-light' : 'phosphor-list-checks-light'" aria-hidden="true" />
            @if($primary)
                {{ $row['isCard'] ? 'Respuestas' : 'Invitados' }}
            @endif
        </a>
    @else
        <span class="ev-act is-off" role="img" title="La lista de invitados y las confirmaciones vienen en el paquete Premium." aria-label="La lista de invitados y las confirmaciones vienen en el paquete Premium.">
            <x-phosphor-lock-simple-light aria-hidden="true" />
        </span>
    @endcan

    {{-- Solo el revendedor al día edita lo suyo; al cliente normal se la arma el equipo --}}
    @can('update', $invitation)
        <a href="{{ route('client.invitations.edit', $invitation) }}" class="ev-act" title="Editar" aria-label="Editar">
            <x-phosphor-pencil-simple-line-light aria-hidden="true" />
        </a>
    @endcan

    {{-- «Hazlo tú»: su dueño la edita siempre --}}
    @can('editOwn', $invitation)
        @if($row['isDraft'])
            <a href="{{ route('client.diy.publish', $invitation) }}" class="ev-act" title="Publicar" aria-label="Publicar">
                <x-phosphor-paper-plane-tilt-light aria-hidden="true" />
            </a>
        @endif
        <a href="{{ route('client.diy.edit', $invitation) }}" class="ev-act" title="{{ $row['isDraft'] ? 'Continuar' : 'Editar' }}" aria-label="{{ $row['isDraft'] ? 'Continuar' : 'Editar' }}">
            <x-phosphor-pencil-simple-line-light aria-hidden="true" />
        </a>
    @endcan

    @if($row['publicUrl'])
        <a href="{{ $row['publicUrl'] }}" target="_blank" rel="noopener" class="ev-act" title="Abrir mi {{ mb_strtolower($row['kindLabel']) }}" aria-label="Abrir mi {{ mb_strtolower($row['kindLabel']) }}">
            <x-phosphor-arrow-square-out-light aria-hidden="true" />
        </a>
        <x-ui.copy-button :text="$row['publicUrl']" label="Copiar enlace" done="Enlace copiado" icon="copy-light" plain class="ev-act" title="Copiar enlace" />
        <a href="{{ $row['shareUrl'] }}" target="_blank" rel="noopener" class="ev-act" title="Mandar por WhatsApp" aria-label="Mandar por WhatsApp">
            <x-phosphor-whatsapp-logo-light aria-hidden="true" />
        </a>
    @endif

    {{-- La armó el equipo: un cambio se pide por WhatsApp, con el nombre del evento ya escrito --}}
    @if($row['helpUrl'])
        <a href="{{ $row['helpUrl'] }}" target="_blank" rel="noopener" class="ev-act" title="Pedir un cambio" aria-label="Pedir un cambio">
            <x-phosphor-chat-circle-dots-light aria-hidden="true" />
        </a>
    @endif

    {{-- «Hazlo tú»: un borrador que nunca se publicó también se borra --}}
    @can('deleteOwn', $invitation)
        <form method="POST" action="{{ route('client.diy.destroy', $invitation) }}" class="ev-acts__end"
            onsubmit="return confirm(@js('¿Borrar el borrador «'.$invitation->title.'»? No se puede recuperar.'))">
            @csrf
            @method('DELETE')
            <button type="submit" class="ev-act is-danger" title="Borrar borrador" aria-label="Borrar borrador">
                <x-phosphor-trash-light aria-hidden="true" />
            </button>
        </form>
    @endcan
</div>
