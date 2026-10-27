{{--
    Editor del revendedor sobre una invitación suya: los mismos paneles que el del administrador,
    sin la sección de clientes y con las rutas bajo /client.
--}}
@extends('layouts.admin-editor', ['editorHome' => route('client.dashboard')])

@section('title', 'Editar invitación')
@section('header-title', $invitation->title)

@section('header-links')
    <a href="{{ route('invitation.show', $invitation->slug) }}" target="_blank" rel="noopener" class="ed-act" title="Ver pública">
        <x-phosphor-arrow-square-out-light aria-hidden="true" />
        <span class="ed-act__text">Ver pública</span>
    </a>
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
        'formAction' => route('client.invitations.update', $invitation),
        'formMethod' => 'PUT',
        'isCreate' => false,
    ])
@endsection
