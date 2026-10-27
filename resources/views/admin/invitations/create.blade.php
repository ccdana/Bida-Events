@extends('layouts.admin-editor')

@section('title', 'Nueva invitación')
@section('header-title', 'Crear invitación')

@section('header-actions')
    <a href="{{ route('admin.dashboard') }}" class="ed-act ed-act--solo" title="Cancelar" aria-label="Cancelar">
        <x-phosphor-x-light aria-hidden="true" />
    </a>
    {{-- La crea el asistente (editor/wizard); si vuelve con errores, «Crear invitación» está en la barra del editor --}}
@endsection

@section('content')
    @include('admin.invitations._form', [
        'formAction' => route('admin.invitations.store'),
        'formMethod' => 'POST',
        'isCreate' => true,
        'cancelUrl' => route('admin.dashboard'),
    ])
@endsection
