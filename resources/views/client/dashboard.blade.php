@extends('layouts.client')

@section('title', 'Mis Eventos')

@section('content')
<div class="mb-8">
    <h1 class="text-4xl font-serif font-bold client-main-title mb-3">Mis Eventos</h1>
    <p class="text-lg client-subtitle">Administra tus invitaciones, confirmaciones y reportes</p>
</div>

<div class="space-y-4">
    @forelse($invitations as $invitation)
        <article class="client-card rounded-2xl border p-6 transition hover:shadow-lg">
            <div class="flex items-start justify-between gap-4 mb-4">
                <div class="flex-1">
                    <h2 class="text-2xl font-serif font-semibold">{{ $invitation->title }}</h2>
                    <p class="text-sm opacity-70 mt-1">
                        📅 {{ $invitation->event_date->format('d \\d\\e F, Y') }} a las {{ $invitation->event_date->format('H:i') }}
                    </p>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-medium" 
                      :class="'{{ strtolower($invitation->status) }}' === 'active' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-stone-200 text-stone-700 dark:bg-stone-700 dark:text-stone-300'">
                    @switch($invitation->status)
                        @case('draft') Borrador @break
                        @case('active') Activa @break
                        @case('suspended') Suspendida @break
                        @case('expired') Expirada @break
                        @default {{ $invitation->status }}
                    @endswitch
                </span>
            </div>
            
            <div class="flex flex-wrap gap-3 mb-6">
                @php
                    $confirmed = $invitation->guests->where('status', 'confirmed')->count();
                    $pending = $invitation->guests->where('status', 'pending')->count();
                    $declined = $invitation->guests->where('status', 'declined')->count();
                @endphp
                <span class="client-badge-confirmed px-3 py-2 rounded-full text-sm font-medium inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/></svg>
                    {{ $confirmed }} {{ $confirmed === 1 ? 'confirmado' : 'confirmados' }}
                </span>
                @if($pending > 0)
                    <span class="client-badge-pending px-3 py-2 rounded-full text-sm font-medium inline-flex items-center gap-2">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 100-2 1 1 0 000 2zm6 0a1 1 0 100-2 1 1 0 000 2zm-5 5a1 1 0 11-2 0 1 1 0 012 0z"/></svg>
                        {{ $pending }} pendiente{{ $pending !== 1 ? 's' : '' }}
                    </span>
                @endif
                @if($declined > 0)
                    <span class="px-3 py-2 rounded-full text-sm font-medium inline-flex items-center gap-2 bg-red-50 text-red-800 dark:bg-red-950 dark:text-red-300">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"/></svg>
                        {{ $declined }} declinado{{ $declined !== 1 ? 's' : '' }}
                    </span>
                @endif
            </div>
            
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('client.invitation.show', $invitation) }}" class="client-btn-primary px-4 py-2 rounded-lg text-sm font-medium transition inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    Ver detalle
                </a>
                <a href="{{ route('client.export.excel', $invitation) }}" class="client-btn-secondary px-4 py-2 rounded-lg text-sm font-medium border transition inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Excel
                </a>
                <a href="{{ route('client.export.pdf', $invitation) }}" class="client-btn-secondary px-4 py-2 rounded-lg text-sm font-medium border transition inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    PDF confirmados
                </a>
                <a href="{{ route('client.export.invitation-pdf', $invitation) }}" class="px-4 py-2 rounded-lg text-sm font-medium border transition inline-flex items-center gap-2" 
                   style="border-color: rgb(217 119 6); color: rgb(217 119 6);" onmouseover="this.style.backgroundColor = 'rgba(217, 119, 6, 0.1)'" onmouseout="this.style.backgroundColor = 'transparent'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/></svg>
                    PDF invitación
                </a>
            </div>
        </article>
    @empty
        <div class="client-card rounded-2xl border p-12 text-center">
            <svg class="w-16 h-16 mx-auto mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3v-6"/></svg>
            <h3 class="text-lg font-serif font-semibold mb-2">No hay eventos</h3>
            <p class="opacity-70">Contacta con el equipo de Bida Events para crear tu primer evento.</p>
        </div>
    @endempty
</div>
@endsection
