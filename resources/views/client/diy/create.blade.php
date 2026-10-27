{{--
    «Hazlo tú», invitación nueva: el mismo editor del equipo (admin.invitations._form) con todo
    desbloqueado. Nace como borrador; al publicarla se elige gratis o el nivel que piden sus partes.
--}}
@extends('layouts.admin-editor', ['editorHome' => route('client.dashboard')])

@section('title', 'Nueva invitación')
@section('header-title', 'Crear mi invitación')

@section('header-actions')
    <a href="{{ route('client.dashboard') }}" class="ed-act ed-act--solo" title="Cancelar" aria-label="Cancelar">
        <x-phosphor-x-light aria-hidden="true" />
    </a>
@endsection

@section('content')
    @include('admin.invitations._form', [
        'formAction' => route('client.diy.store'),
        'formMethod' => 'POST',
        'isCreate' => true,
        'cancelUrl' => route('client.dashboard'),
    ])
@endsection
