{{--
    «Hazlo tú» sobre una invitación propia: el mismo editor del equipo, con todo desbloqueado y el
    nivel de cada parte a la vista. «Publicar» (barra del editor) pasa por client.diy.publish.
--}}
@extends('layouts.admin-editor', ['editorHome' => route('client.dashboard')])

@section('title', 'Editar mi invitación')
@section('header-title', $invitation->title)

@section('header-actions')
    @if($invitation->status === 'active')
        <a href="{{ route('invitation.show', $invitation->slug) }}" target="_blank" rel="noopener" class="admin-link-button">
            <x-phosphor-arrow-square-out aria-hidden="true" />
            <span class="hidden md:inline">Ver pública</span>
        </a>
    @endif
    <a href="{{ route('client.invitation.show', $invitation) }}" class="admin-link-button">
        <x-phosphor-users aria-hidden="true" />
        <span class="hidden md:inline">Invitados</span>
    </a>
    <button type="button" onclick="document.getElementById('invitation-form').requestSubmit()" class="admin-primary-button">
        <x-phosphor-floppy-disk aria-hidden="true" />
        Guardar
    </button>
@endsection

@section('content')
    @include('admin.invitations._form', [
        'formAction' => route('client.diy.update', $invitation),
        'formMethod' => 'PUT',
        'isCreate' => false,
    ])
@endsection
