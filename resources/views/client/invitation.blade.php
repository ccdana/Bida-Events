@extends('layouts.client')

@section('title', $invitation->title)

@section('content')
    <header class="site-enter">
        <a href="{{ route('client.dashboard') }}" class="inline-flex items-center gap-2 text-sm text-site-muted transition-colors hover:text-site-ink">
            <x-phosphor-arrow-left class="size-4" aria-hidden="true" />
            Mis eventos
        </a>
        <div class="mt-4 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div class="min-w-0">
                <h1 class="truncate text-3xl font-semibold tracking-tight md:text-4xl">{{ $invitation->title }}</h1>
                @if($invitation->event_date)
                    <p class="mt-2 inline-flex items-center gap-2 text-site-muted">
                        <x-phosphor-calendar-blank class="size-5 shrink-0" aria-hidden="true" />
                        {{ $invitation->event_date->locale('es')->translatedFormat('j \d\e F \d\e Y') }}, {{ $invitation->event_date->format('H:i') }}
                    </p>
                @endif
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('client.export.excel', $invitation) }}" class="admin-link-button">
                    <x-phosphor-file-xls aria-hidden="true" />
                    Descargar Excel
                </a>
                <a href="{{ route('client.export.pdf', $invitation) }}" class="admin-link-button">
                    <x-phosphor-file-pdf aria-hidden="true" />
                    Descargar PDF
                </a>
            </div>
        </div>
    </header>

    <dl class="site-enter mt-10 grid grid-cols-2 gap-y-6 border-y border-site-line py-6 lg:grid-cols-4" style="--enter-index: 1">
        @foreach([
            'Confirmados' => $confirmed->count(),
            'Pases confirmados' => $totalPasses,
            'Pendientes' => $pending->count(),
            'Pases cubiertos' => $confirmationRate.'%',
        ] as $label => $value)
            <div class="lg:border-l lg:border-site-line lg:px-6 lg:first:border-l-0 lg:first:pl-0">
                <dt class="admin-metric-label">{{ $label }}</dt>
                <dd class="admin-metric-value">{{ $value }}</dd>
            </div>
        @endforeach
    </dl>

    <section class="site-enter mt-10 overflow-hidden rounded-[16px] border border-site-line bg-site-surface" style="--enter-index: 2">
        <div class="overflow-x-auto">
            <table class="adm-table">
                <thead>
                    <tr>
                        <th scope="col">Invitado</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Pases</th>
                        <th scope="col">Alimentación</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td class="font-medium">{{ $row['guest']->name }}</td>
                            <td>
                                <span class="admin-status-badge {{ $row['statusClass'] }}">
                                    <span class="admin-status-dot"></span>
                                    {{ $row['statusLabel'] }}
                                </span>
                            </td>
                            <td class="tabular-nums">{{ $row['passesLabel'] }}</td>
                            <td class="text-site-muted">{{ $row['dietaryRestrictions'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="flex flex-col items-center py-12 text-center">
                                    <x-phosphor-users-three-light class="size-12 text-site-accent" aria-hidden="true" />
                                    <p class="mt-4 font-medium">Todavía no hay invitados</p>
                                    <p class="mt-1 text-site-muted">Cuando el equipo los agregue, verás aquí sus confirmaciones.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
