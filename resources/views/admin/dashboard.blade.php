@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-serif text-stone-900">Panel Administrativo</h1>
    <p class="text-stone-500 mt-1">Gestiona invitaciones, invitados y configuraciones.</p>
</div>

<div class="space-y-4">
    @forelse($invitations as $invitation)
        <article class="rounded-2xl border border-stone-200 bg-white p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-medium text-stone-900">{{ $invitation->title }}</h2>
                <p class="text-sm text-stone-500 mt-1">
                    {{ $invitation->eventType->name }} · {{ $invitation->event_date->format('d/m/Y') }}
                    · <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs {{ $invitation->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-stone-100 text-stone-600' }}">{{ ucfirst($invitation->status) }}</span>
                </p>
                <p class="text-xs text-stone-400 mt-2">{{ $invitation->guests->count() }} invitados · /p/{{ $invitation->slug }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('invitation.show', $invitation->slug) }}" target="_blank" class="px-4 py-2 text-sm border border-stone-300 rounded-lg hover:bg-stone-50 transition">Ver pública</a>
                <a href="{{ route('admin.invitations.edit', $invitation) }}" class="px-4 py-2 text-sm bg-stone-900 text-white rounded-lg hover:bg-stone-800 transition">Editar</a>
                <a href="{{ route('admin.guests.index', $invitation) }}" class="px-4 py-2 text-sm border border-amber-300 text-amber-900 rounded-lg hover:bg-amber-50 transition">Invitados</a>
            </div>
        </article>
    @empty
        <p class="text-stone-500">No hay invitaciones. Ejecuta el seeder para cargar datos de prueba.</p>
    @endforelse
</div>
@endsection
