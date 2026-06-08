@extends('layouts.admin-editor')

@section('title', 'Editar invitación')
@section('header-title', $invitation->title)

@section('header-actions')
    <a href="{{ route('invitation.show', $invitation->slug) }}" target="_blank" class="admin-link-button">Ver pública</a>
    <a href="{{ route('admin.guests.index', $invitation) }}" class="admin-link-button">Invitados</a>
    <button form="invitation-form" type="submit" class="admin-primary-button">Guardar</button>
@endsection

@section('content')
    @include('admin.invitations._form', [
        'formAction' => route('admin.invitations.update', $invitation),
        'formMethod' => 'PUT',
        'isCreate' => false,
    ])
@endsection
