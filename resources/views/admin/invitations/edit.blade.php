@extends('layouts.admin-editor')

@section('title', 'Editar invitación')
@section('header-title', $invitation->title)

@section('header-actions')
    <a href="{{ route('invitation.show', $invitation->slug) }}" target="_blank" rel="noopener" class="admin-link-button">
        <x-phosphor-arrow-square-out aria-hidden="true" />
        <span class="hidden md:inline">Ver pública</span>
    </a>
    <a href="{{ route('admin.guests.index', $invitation) }}" class="admin-link-button">
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
        'formAction' => route('admin.invitations.update', $invitation),
        'formMethod' => 'PUT',
        'isCreate' => false,
    ])
@endsection
