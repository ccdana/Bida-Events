@extends('layouts.client')

@section('title', 'Mis Eventos')

@section('content')
<div class="mb-8">
    <h1 class="text-4xl font-bold client-page-title mb-3">Mis Eventos</h1>
    <p class="text-lg client-subtitle">Administra tus invitaciones, confirmaciones y reportes</p>
</div>

<div class="space-y-4">
    @forelse($invitations as $invitation)
        @php
            $confirmed = $invitation->guests->where('status', 'confirmed')->count();
            $pending = $invitation->guests->where('status', 'pending')->count();
            $declined = $invitation->guests->where('status', 'declined')->count();
            $status = strtolower($invitation->status);
            $statusLabel = match ($status) {
                'draft' => 'Borrador',
                'active' => 'Activa',
                'suspended' => 'Suspendida',
                'expired' => 'Expirada',
                default => $invitation->status,
            };
            $statusClass = match ($status) {
                'active' => 'is-success',
                'draft' => 'is-warning',
                'suspended', 'expired' => 'is-danger',
                default => 'is-primary',
            };
        @endphp

        <article class="client-card p-6 transition hover:-translate-y-0.5 hover:shadow-lg">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between mb-5">
                <div class="flex-1 min-w-0">
                    <h2 class="text-2xl font-bold client-page-title truncate">{{ $invitation->title }}</h2>
                    <p class="text-sm client-muted mt-2 inline-flex items-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>{{ $invitation->event_date->format('d \d\e F, Y') }} a las {{ $invitation->event_date->format('H:i') }}</span>
                    </p>
                </div>
                <span class="client-pill {{ $statusClass }} self-start">
                    {{ $statusLabel }}
                </span>
            </div>

            <div class="grid gap-3 sm:grid-cols-3 mb-6">
                <div class="client-stat-card p-4 text-center">
                    <p class="client-stat-value text-3xl font-semibold">{{ $confirmed }}</p>
                    <p class="client-stat-label text-sm mt-1">Confirmados</p>
                </div>
                <div class="client-stat-card p-4 text-center">
                    <p class="client-stat-value text-3xl font-semibold">{{ $pending }}</p>
                    <p class="client-stat-label text-sm mt-1">Pendientes</p>
                </div>
                <div class="client-stat-card p-4 text-center">
                    <p class="client-stat-value text-3xl font-semibold">{{ $declined }}</p>
                    <p class="client-stat-label text-sm mt-1">Declinados</p>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('client.invitation.show', $invitation) }}" class="client-button-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    Ver detalle
                </a>
                <a href="{{ route('client.export.excel', $invitation) }}" class="client-button-secondary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Excel
                </a>
                <a href="{{ route('client.export.pdf', $invitation) }}" class="client-button-secondary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    PDF confirmados
                </a>
                <a href="{{ route('client.export.invitation-pdf', $invitation) }}" class="client-button-secondary client-button-accent">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                    </svg>
                    PDF invitación
                </a>
            </div>
        </article>
    @empty
        <div class="client-card p-12 text-center">
            <svg class="w-16 h-16 mx-auto mb-4 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3v-6"/>
            </svg>
            <h3 class="text-lg font-semibold client-page-title mb-2">No hay eventos</h3>
            <p class="client-subtitle">Contacta con el equipo de Bida Events para crear tu primer evento.</p>
        </div>
    @endforelse
</div>
@endsection
