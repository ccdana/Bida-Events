{{--
    «Hazlo tú» sobre una invitación propia: el mismo editor del equipo, con todo desbloqueado y el
    nivel de cada parte a la vista. «Publicar» (barra del editor) pasa por client.diy.publish.
--}}
@extends('layouts.admin-editor', ['editorHome' => route('client.dashboard')])

@section('title', 'Editar mi invitación')
@section('header-title', $invitation->title)

@section('header-links')
    @if($invitation->status === 'active')
        <a href="{{ route('invitation.show', $invitation->slug) }}" target="_blank" rel="noopener" class="ed-act" title="Ver pública">
            <x-phosphor-arrow-square-out-light aria-hidden="true" />
            <span class="ed-act__text">Ver pública</span>
        </a>
    @endif
    <a href="{{ route('client.invitation.show', $invitation) }}" class="ed-act" title="Invitados">
        <x-phosphor-users-light aria-hidden="true" />
        <span class="ed-act__text">Invitados</span>
    </a>
@endsection

@section('header-actions')
    <button type="button" onclick="document.getElementById('invitation-form').requestSubmit()" class="admin-primary-button ed-save" title="Guardar">
        <x-phosphor-floppy-disk-light aria-hidden="true" />
        <span class="ed-act__text">Guardar</span>
    </button>
@endsection

@section('content')
    @include('admin.invitations._form', [
        'formAction' => route('client.diy.update', $invitation),
        'formMethod' => 'PUT',
        'isCreate' => false,
    ])
@endsection
