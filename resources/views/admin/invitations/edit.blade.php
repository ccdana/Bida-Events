@extends('layouts.admin-editor')

@section('title', 'Editar invitación')
@section('header-title', $invitation->title)

@section('header-actions')
    <a href="{{ route('invitation.show', $invitation->slug) }}" target="_blank" class="text-xs text-amber-800 hover:underline uppercase tracking-wider">Ver pública</a>
    <a href="{{ route('admin.guests.index', $invitation) }}" class="text-xs text-stone-600 hover:text-stone-900 uppercase tracking-wider">Invitados</a>
    <button form="invitation-form" type="submit" class="px-4 py-2 bg-stone-900 text-white text-sm rounded-xl hover:bg-stone-800 transition font-medium">Guardar</button>
@endsection

@section('content')
    @include('admin.invitations._form', [
        'formAction' => route('admin.invitations.update', $invitation),
        'formMethod' => 'PUT',
        'isCreate' => false,
    ])
@endsection
