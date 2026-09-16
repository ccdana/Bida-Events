@extends('layouts.admin')

@section('title', 'Invitados')

@php
    $statuses = [
        'confirmed' => ['Confirmado', 'is-confirmed'],
        'declined' => ['No asiste', 'is-declined'],
        'pending' => ['Pendiente', 'is-pending'],
    ];
@endphp

@section('content')
    <div class="grid gap-10">
        <header class="site-enter">
            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 text-sm text-site-muted transition-colors hover:text-site-ink">
                <x-phosphor-arrow-left class="size-4" aria-hidden="true" />
                Invitaciones
            </a>
            <div class="mt-4 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div class="min-w-0">
                    <h1 class="truncate text-3xl font-semibold tracking-tight md:text-4xl">{{ $invitation->title }}</h1>
                    <p class="mt-2 text-site-muted">{{ $guests->total() }} invitados. Cada uno recibe su propio enlace para confirmar.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('invitation.show', $invitation->slug) }}" target="_blank" rel="noopener" class="admin-link-button">
                        <x-phosphor-arrow-square-out aria-hidden="true" />
                        Ver invitación
                    </a>
                    <a href="{{ route('admin.invitations.edit', $invitation) }}" class="admin-link-button">
                        <x-phosphor-pencil-simple aria-hidden="true" />
                        Editar
                    </a>
                </div>
            </div>
        </header>

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_21rem] lg:items-start">
            <section class="site-enter overflow-hidden rounded-[16px] border border-site-line bg-site-surface" style="--enter-index: 1">
                {{-- Buscar por nombre y filtrar por estado; ambos viajan en la URL para poder compartirla --}}
                <form method="GET" class="flex flex-wrap items-end gap-3 border-b border-site-line p-4">
                    <div class="min-w-[12rem] flex-1">
                        <label for="buscar-invitado" class="admin-label">Buscar</label>
                        <input id="buscar-invitado" type="search" name="q" value="{{ $search }}" placeholder="Nombre del invitado" class="admin-input">
                    </div>
                    <div>
                        <label for="filtrar-estado" class="admin-label">Estado</label>
                        <select id="filtrar-estado" name="estado" class="admin-input">
                            <option value="">Todos</option>
                            @foreach(['confirmed' => 'Confirmados', 'pending' => 'Pendientes', 'declined' => 'No asisten'] as $value => $label)
                                <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="admin-link-button">
                        <x-phosphor-magnifying-glass aria-hidden="true" />
                        Filtrar
                    </button>
                    @if($search !== '' || $status !== '')
                        <a href="{{ route('admin.guests.index', $invitation) }}" class="admin-link-button">Limpiar</a>
                    @endif
                </form>

                <div class="overflow-x-auto">
                    <table class="adm-table">
                        <thead>
                            <tr>
                                <th scope="col">Nombre</th>
                                <th scope="col">Pases</th>
                                <th scope="col">Estado</th>
                                <th scope="col"><span class="sr-only">Acciones</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($guests as $guest)
                                @php([$statusLabel, $statusClass] = $statuses[$guest->status] ?? [ucfirst((string) $guest->status), 'is-pending'])
                                <tr>
                                    <td class="font-medium">
                                        {{ $guest->name }}
                                        @if($guest->phone)
                                            <span class="mt-0.5 block text-sm font-normal text-site-muted">{{ $guest->phone }}</span>
                                        @endif
                                    </td>
                                    <td class="tabular-nums">{{ $guest->passes_confirmed }}/{{ $guest->passes_allocated }}</td>
                                    <td>
                                        <span class="admin-status-badge {{ $statusClass }}">
                                            <span class="admin-status-dot"></span>
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="flex justify-end gap-1">
                                            <a href="{{ route('invitation.guest', [$invitation->slug, $guest->qr_code_token]) }}" target="_blank" rel="noopener"
                                                class="admin-icon-button" aria-label="Abrir el enlace de {{ $guest->name }}" title="Abrir enlace personal">
                                                <x-phosphor-link-simple aria-hidden="true" />
                                            </a>
                                            <form method="POST" action="{{ route('admin.guests.token', [$invitation, $guest]) }}"
                                                onsubmit="return confirm('¿Generar un enlace nuevo para {{ e($guest->name) }}? El anterior dejará de funcionar.')">
                                                @csrf
                                                <button type="submit" class="admin-icon-button" aria-label="Generar un enlace nuevo para {{ $guest->name }}" title="Generar enlace nuevo">
                                                    <x-phosphor-arrows-clockwise aria-hidden="true" />
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.guests.destroy', [$invitation, $guest]) }}" onsubmit="return confirm('¿Eliminar a {{ e($guest->name) }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="admin-icon-button is-danger" aria-label="Eliminar a {{ $guest->name }}" title="Eliminar">
                                                    <x-phosphor-trash aria-hidden="true" />
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">
                                        <div class="flex flex-col items-center py-12 text-center">
                                            <x-phosphor-users-three-light class="size-12 text-site-accent" aria-hidden="true" />
                                            <p class="mt-4 font-medium">Todavía no hay invitados</p>
                                            <p class="mt-1 text-site-muted">
                                                {{ $search !== '' || $status !== '' ? 'Ningún invitado coincide con la búsqueda.' : 'Agrega el primero con el formulario.' }}
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-4 pb-4">
                    @include('layouts.partials.pagination', ['paginator' => $guests])
                </div>
            </section>

            <aside class="site-enter admin-card p-6 lg:sticky lg:top-24" style="--enter-index: 2">
                <h2 class="text-lg font-semibold tracking-tight">Agregar invitado</h2>
                <p class="mt-1 text-sm text-site-muted">Recibirá un enlace personal con sus pases.</p>

                <form method="POST" action="{{ route('admin.guests.store', $invitation) }}" class="mt-6 grid gap-4">
                    @csrf
                    <div>
                        <label for="guest-name" class="admin-label">Nombre o familia</label>
                        <input id="guest-name" type="text" name="name" value="{{ old('name') }}" required class="admin-input" placeholder="Familia Quispe">
                        @error('name')
                            <p class="mt-1.5 text-sm text-site-danger">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="guest-phone" class="admin-label">Teléfono <span class="font-normal text-site-muted">(opcional)</span></label>
                        <input id="guest-phone" type="tel" name="phone" value="{{ old('phone') }}" inputmode="tel" class="admin-input" placeholder="71234567">
                        @error('phone')
                            <p class="mt-1.5 text-sm text-site-danger">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="guest-passes" class="admin-label">Pases asignados</label>
                        <input id="guest-passes" type="number" name="passes_allocated" value="{{ old('passes_allocated', 1) }}" min="1" max="20" class="admin-input">
                        @error('passes_allocated')
                            <p class="mt-1.5 text-sm text-site-danger">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="admin-primary-button mt-2 min-h-11 w-full">
                        <x-phosphor-user-plus aria-hidden="true" />
                        Agregar invitado
                    </button>
                </form>
            </aside>
        </div>
    </div>
@endsection
