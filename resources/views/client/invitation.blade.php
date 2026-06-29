@extends('layouts.client')

@section('title', $invitation->title)

@section('content')
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div class="min-w-0">
        <a href="{{ route('client.dashboard') }}" class="text-sm font-medium client-muted hover:text-stone-900 transition">Mis eventos</a>
        <h1 class="mt-2 text-3xl font-bold client-page-title truncate">{{ $invitation->title }}</h1>
        <p class="mt-2 client-muted">
            {{ $invitation->event_date->format('d \d\e F, Y') }} a las {{ $invitation->event_date->format('H:i') }}
        </p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('client.export.excel', $invitation) }}" class="client-button-secondary">Descargar Excel</a>
        <a href="{{ route('client.export.pdf', $invitation) }}" class="client-button-secondary">Descargar PDF</a>
    </div>
</div>

<div class="grid gap-4 sm:grid-cols-4 mb-8">
    <div class="client-stat-card p-4 text-center">
        <p class="client-stat-value text-3xl font-semibold">{{ $confirmed->count() }}</p>
        <p class="client-stat-label text-sm mt-1">Confirmados</p>
    </div>
    <div class="client-stat-card p-4 text-center">
        <p class="client-stat-value text-3xl font-semibold">{{ $totalPasses }}</p>
        <p class="client-stat-label text-sm mt-1">Pases confirmados</p>
    </div>
    <div class="client-stat-card p-4 text-center">
        <p class="client-stat-value text-3xl font-semibold">{{ $pending->count() }}</p>
        <p class="client-stat-label text-sm mt-1">Pendientes</p>
    </div>
    <div class="client-stat-card p-4 text-center">
        <p class="client-stat-value text-3xl font-semibold">{{ $confirmationRate }}%</p>
        <p class="client-stat-label text-sm mt-1">Cobertura</p>
    </div>
</div>

<div class="client-table-shell">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="client-table-head text-left text-xs uppercase tracking-wide text-stone-500">
                <tr>
                    <th class="px-4 py-3 font-semibold">Invitado</th>
                    <th class="px-4 py-3 font-semibold">Estado</th>
                    <th class="px-4 py-3 font-semibold">Pases</th>
                    <th class="px-4 py-3 font-semibold">Alimentación</th>
                </tr>
            </thead>
            <tbody>
                @foreach($guests as $guest)
                    <tr class="client-table-row">
                        <td class="px-4 py-3 font-medium text-stone-900">{{ $guest->name }}</td>
                        <td class="px-4 py-3 text-stone-600">{{ ucfirst($guest->status) }}</td>
                        <td class="px-4 py-3 text-stone-600">{{ $guest->passes_confirmed }}/{{ $guest->passes_allocated }}</td>
                        <td class="px-4 py-3 text-stone-500">{{ $guest->dietary_restrictions ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
