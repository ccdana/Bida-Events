{{--
    Editor del revendedor para una invitación nueva: los mismos paneles que el del administrador
    (admin.invitations._form), sin la sección de clientes y con las rutas bajo /client.
--}}
@extends('layouts.admin-editor', ['editorHome' => route('client.dashboard')])

@section('title', 'Nueva invitación')
@section('header-title', 'Crear invitación')

@section('header-actions')
    <a href="{{ route('client.dashboard') }}" class="admin-link-button">Cancelar</a>
    <button type="button" onclick="document.getElementById('invitation-form').requestSubmit()" class="admin-primary-button">
        <x-phosphor-plus-bold aria-hidden="true" />
        Crear invitación
    </button>
@endsection

@section('content')
    @include('admin.invitations._form', [
        'formAction' => route('client.invitations.store'),
        'formMethod' => 'POST',
        'isCreate' => true,
    ])
@endsection
