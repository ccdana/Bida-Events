@extends('layouts.client')

@section('title', 'Mis eventos')

@section('content')
    <header class="site-enter">
        <h1 class="text-3xl font-semibold tracking-tight md:text-4xl">Mis eventos</h1>
        <p class="mt-2 max-w-[52ch] text-site-muted">Revisa quién confirmó asistencia y descarga tus reportes.</p>
    </header>

    @include('client.partials.export-status')

    <div class="mt-10 grid gap-5">
        @forelse($items as $index => $row)
            @php($invitation = $row['invitation'])
            <article class="site-enter admin-card p-6 lg:p-8" style="--enter-index: {{ $index + 1 }}">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <h2 class="truncate text-2xl font-semibold tracking-tight">{{ $invitation->title }}</h2>
                        @if($invitation->event_date)
                            <p class="mt-2 inline-flex items-center gap-2 text-site-muted">
                                <x-phosphor-calendar-blank class="size-5 shrink-0" aria-hidden="true" />
                                {{ $invitation->event_date->locale('es')->translatedFormat('j \d\e F \d\e Y') }}, {{ $invitation->event_date->format('H:i') }}
                            </p>
                        @endif
                    </div>
                    <span class="admin-status-badge {{ $row['statusClass'] }} self-start">
                        <span class="admin-status-dot"></span>
                        {{ $row['statusLabel'] }}
                    </span>
                </div>

                <dl class="mt-8 grid grid-cols-3 border-y border-site-line py-5">
                    @foreach(['Confirmados' => $row['confirmed'], 'Pendientes' => $row['pending'], 'No asisten' => $row['declined']] as $label => $value)
                        <div class="border-site-line px-4 first:pl-0 [&:not(:first-child)]:border-l">
                            <dt class="text-sm text-site-muted">{{ $label }}</dt>
                            <dd class="mt-1 text-3xl font-semibold tracking-tight tabular-nums">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>

                <div class="mt-6 flex flex-wrap gap-2">
                    <a href="{{ route('client.invitation.show', $invitation) }}" class="admin-primary-button">
                        <x-phosphor-list-checks aria-hidden="true" />
                        Ver invitados
                    </a>
                    @include('client.partials.export-buttons', ['invitation' => $invitation])
                </div>
            </article>
        @empty
            <div class="site-enter admin-card flex flex-col items-center px-6 py-16 text-center">
                <x-phosphor-calendar-blank-light class="size-12 text-site-accent" aria-hidden="true" />
                <h2 class="mt-4 text-lg font-medium">Todavía no tienes eventos</h2>
                <p class="mt-1 max-w-[40ch] text-site-muted">Cuando armemos tu invitación, aparecerá aquí con sus confirmaciones.</p>
                <a href="{{ route('home') }}#contacto" class="admin-link-button mt-6">Contactar al equipo</a>
            </div>
        @endforelse
    </div>
@endsection
