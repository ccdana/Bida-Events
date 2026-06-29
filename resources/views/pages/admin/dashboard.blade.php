@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <section class="admin-card overflow-hidden">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-2xl">
                <p class="admin-eyebrow">Resumen</p>
                <h1 class="font-serif text-4xl text-stone-950">Invitaciones</h1>
                <p class="mt-2 text-sm leading-relaxed text-stone-500">Gestión limpia, sin paneles pesados. Revisa el estado de cada evento y entra directo al editor.</p>
            </div>
        </div>
    </section>

    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div class="admin-card admin-metric-card py-4">
            <p class="admin-metric-label">Total</p>
            <p class="admin-metric-value">{{ $metrics['total'] }}</p>
        </div>
        <div class="admin-card admin-metric-card py-4">
            <p class="admin-metric-label">Activas</p>
            <p class="admin-metric-value">{{ $metrics['active'] }}</p>
        </div>
        <div class="admin-card admin-metric-card py-4">
            <p class="admin-metric-label">Borradores</p>
            <p class="admin-metric-value">{{ $metrics['draft'] }}</p>
        </div>
        <div class="admin-card admin-metric-card py-4">
            <p class="admin-metric-label">Invitados</p>
            <p class="admin-metric-value">{{ $metrics['guests'] }}</p>
        </div>
    </section>

    <section class="admin-card p-0 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-stone-200">
            <div>
                <p class="admin-label mb-1">Listado</p>
                <h2 class="font-serif text-xl text-stone-950">Últimas invitaciones</h2>
            </div>
            <span class="text-xs uppercase tracking-widest text-stone-400">{{ $metrics['total'] }} registros</span>
        </div>

        <div class="divide-y divide-stone-200">
            @forelse($items as $row)
                @php $invitation = $row['invitation']; @endphp
                <article class="px-5 py-4 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="min-w-0">
                        <div class="flex items-center gap-3 flex-wrap">
                            <h3 class="text-lg font-medium text-stone-950 truncate">{{ $invitation->title }}</h3>
                            <span class="admin-status-badge {{ $row['statusClass'] }}">
                                <span class="admin-status-dot"></span>
                                {{ $invitation->status }}
                            </span>
                        </div>
                        <p class="mt-1 text-sm text-stone-500">{{ $row['eventTypeName'] }} · {{ $row['eventDateLabel'] }}</p>
                        <p class="mt-2 text-xs text-stone-400 font-mono">/p/{{ $invitation->slug }} · {{ $row['guestCount'] }} invitados</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('invitation.show', $invitation->slug) }}" target="_blank" class="admin-link-button">Ver</a>
                        <a href="{{ route('admin.invitations.edit', $invitation) }}" class="admin-primary-button">Editar</a>
                        <a href="{{ route('admin.guests.index', $invitation) }}" class="admin-link-button">Invitados</a>
                    </div>
                </article>
            @empty
                <div class="px-5 py-16 text-center">
                    <p class="text-stone-500">Aún no hay invitaciones creadas.</p>
                    <a href="{{ route('admin.invitations.create') }}" class="mt-4 inline-flex text-amber-800 font-medium hover:underline">Crear la primera invitación</a>
                </div>
            @endforelse
        </div>
    </section>
</div>
@endsection
