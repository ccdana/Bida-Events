@extends('layouts.admin-editor')

@section('title', 'Nueva invitación')
@section('header-title', 'Crear invitación')

@section('header-actions')
    <a href="{{ route('admin.dashboard') }}" class="admin-link-button">Cancelar</a>
    <button type="button" onclick="document.getElementById('invitation-form').requestSubmit()" class="admin-primary-button">
        <x-phosphor-plus-bold aria-hidden="true" />
        Crear invitación
    </button>
@endsection

@section('content')
    @include('admin.invitations._form', [
        'formAction' => route('admin.invitations.store'),
        'formMethod' => 'POST',
        'isCreate' => true,
    ])
@endsection
