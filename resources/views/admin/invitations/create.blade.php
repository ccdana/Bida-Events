@extends('layouts.admin-editor')

@section('title', 'Nueva invitación')
@section('header-title', 'Crear invitación')

@section('header-actions')
    <button form="invitation-form" type="submit" class="admin-primary-button">Crear invitación</button>
@endsection

@section('content')
    @include('admin.invitations._form', [
        'formAction' => route('admin.invitations.store'),
        'formMethod' => 'POST',
        'isCreate' => true,
    ])
@endsection
