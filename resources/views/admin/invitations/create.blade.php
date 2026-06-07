@extends('layouts.admin-editor')

@section('title', 'Nueva invitación')
@section('header-title', 'Crear invitación')

@section('header-actions')
    <button form="invitation-form" type="submit" class="px-4 py-2 bg-stone-900 text-white text-sm rounded-xl hover:bg-stone-800 transition font-medium">Crear invitación</button>
@endsection

@section('content')
    @include('admin.invitations._form', [
        'formAction' => route('admin.invitations.store'),
        'formMethod' => 'POST',
        'isCreate' => true,
    ])
@endsection
