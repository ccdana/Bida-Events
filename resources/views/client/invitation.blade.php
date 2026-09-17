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
                @include('client.partials.export-buttons', ['invitation' => $invitation])
            </div>
        </div>
    </header>

    @include('client.partials.export-status')

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

    @if($contributions->isNotEmpty())
        {{-- Lo que suben los invitados: se puede ocultar sin borrarlo --}}
        <section class="site-enter mt-10 overflow-hidden rounded-[16px] border border-site-line bg-site-surface" style="--enter-index: 3">
            <div class="border-b border-site-line px-5 py-4">
                <h2 class="text-lg font-semibold tracking-tight">{{ $contributions->every(fn ($item) => $item['isReply']) ? 'Respuestas a tu tarjeta' : 'Fotos y canciones de tus invitados' }}</h2>
                <p class="mt-1 text-sm text-site-muted">
                    Si algo no te gusta, ocúltalo y deja de verse en la invitación. No se borra: puedes volver a mostrarlo.
                </p>
            </div>

            <ul class="divide-y divide-site-line">
                @foreach($contributions as $item)
                    <li class="flex items-center gap-4 px-5 py-3 {{ $item['isHidden'] ? 'opacity-60' : '' }}">
                        @if($item['url'])
                            <img src="{{ $item['url'] }}" alt="" class="size-14 shrink-0 rounded-[10px] object-cover" loading="lazy" decoding="async">
                        @else
                            <span class="grid size-14 shrink-0 place-items-center rounded-[10px] bg-site-tint text-site-accent">
                                @if($item['isReply'])
                                    <x-phosphor-chat-circle-text class="size-6" aria-hidden="true" />
                                @else
                                    <x-phosphor-music-notes class="size-6" aria-hidden="true" />
                                @endif
                            </span>
                        @endif

                        <div class="min-w-0 flex-1">
                            <p @class(['font-medium', 'truncate' => ! $item['isReply'], 'whitespace-pre-line' => $item['isReply']])>{{ $item['text'] }}</p>
                            <p class="mt-0.5 text-sm text-site-muted">
                                {{ $item['meta'] }}
                                @if($item['isHidden'])
                                    <span class="font-medium text-site-ink">· Oculto</span>
                                @endif
                            </p>
                        </div>

                        <form method="POST" action="{{ route('client.contributions.update', [$invitation, $item['id']]) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="moderation_status" value="{{ $item['isHidden'] ? 'visible' : 'hidden' }}">
                            <button type="submit" class="admin-link-button">{{ $item['isHidden'] ? 'Mostrar' : 'Ocultar' }}</button>
                        </form>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
@endsection
