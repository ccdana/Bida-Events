@extends('layouts.admin')

@section('title', 'Clientes')

{{--
    Clientes: los del equipo y los que crea cada revendedor para sus eventos, con sus eventos y la
    opción de generarles una contraseña nueva si la pierden (se muestra una sola vez).
--}}
@php
    $originTabs = ['' => 'Todos', 'equipo' => 'Del equipo', 'revendedores' => 'De revendedores'];
@endphp

@section('content')
    <div class="grid gap-8">
        <header class="site-enter">
            <h1 class="text-3xl font-semibold tracking-tight md:text-4xl">Clientes</h1>
            <p class="mt-2 max-w-[60ch] text-site-muted">
                Quienes entran a ver sus invitados y descargar sus reportes: los que creas tú desde el editor y los que
                crea cada revendedor para sus eventos.
            </p>
        </header>

        <div class="site-enter grid gap-4" style="--enter-index: 1">
            <nav class="set-tabs" aria-label="Origen de los clientes">
                @foreach($originTabs as $key => $label)
                    <a href="{{ route('admin.clients.index', array_filter(['origen' => $key, 'q' => $search])) }}"
                        @class(['set-tab', 'is-active' => $origin === $key && ! $resellerId]) @if($origin === $key && ! $resellerId) aria-current="page" @endif>
                        {{ $label }} <span class="ml-1 tabular-nums text-site-muted">{{ $counts[$key] }}</span>
                    </a>
                @endforeach
            </nav>

            <form method="GET" action="{{ route('admin.clients.index') }}" class="flex flex-wrap items-end gap-3">
                @if($origin !== '')
                    <input type="hidden" name="origen" value="{{ $origin }}">
                @endif
                <div class="min-w-[14rem] flex-1">
                    <label for="clientes-buscar" class="admin-label">Buscar</label>
                    <input id="clientes-buscar" type="search" name="q" value="{{ $search }}" class="admin-input" placeholder="Nombre o usuario">
                </div>
                @if($resellers->isNotEmpty())
                    <div class="min-w-[14rem]">
                        <label for="clientes-revendedor" class="admin-label">Revendedor</label>
                        <select id="clientes-revendedor" name="revendedor" class="admin-input" onchange="this.form.submit()">
                            <option value="">Todos</option>
                            @foreach($resellers as $reseller)
                                <option value="{{ $reseller->id }}" @selected($resellerId === $reseller->id)>
                                    {{ $reseller->business_name ?: $reseller->name }} ({{ $reseller->reseller_clients_count }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <button type="submit" class="admin-link-button min-h-11">
                    <x-phosphor-magnifying-glass aria-hidden="true" />
                    Buscar
                </button>
            </form>
        </div>

        <section class="site-enter overflow-hidden rounded-[16px] border border-site-line bg-site-surface" style="--enter-index: 2">
            <ul class="divide-y divide-site-line">
                @forelse($clients as $client)
                    <li class="grid gap-3 px-5 py-4 md:grid-cols-[minmax(0,15rem)_minmax(0,1fr)_auto] md:items-start"
                        x-data="clientRow(@js(route('admin.clients.password', $client)), @js($client->name))">
                        <div class="min-w-0">
                            <p class="truncate font-medium">{{ $client->name }}</p>
                            <p class="truncate font-mono text-xs text-site-muted">{{ $client->username }}</p>
                            <p class="mt-2 text-xs">
                                @if($client->createdByReseller)
                                    <span class="admin-status-badge is-primary">
                                        <x-phosphor-storefront class="size-3.5" aria-hidden="true" />
                                        {{ $client->createdByReseller->business_name ?: $client->createdByReseller->name }}
                                    </span>
                                @elseif($client->is_reseller)
                                    <span class="admin-status-badge is-primary">Revendedor, cliente del equipo</span>
                                @else
                                    <span class="admin-status-badge is-success">Del equipo</span>
                                @endif
                            </p>
                        </div>

                        <div class="min-w-0">
                            {{-- Del revendedor solo cuentan aquí las invitaciones del equipo a su nombre --}}
                            @php($events = $client->is_reseller ? $client->invitations->whereNull('reseller_id') : $client->invitations)
                            @forelse($events as $event)
                                <p class="flex min-w-0 items-center gap-2 py-0.5 text-sm">
                                    <span @class(['size-2 shrink-0 rounded-full', 'bg-site-accent' => $event->status === 'active', 'bg-site-line' => $event->status !== 'active']) aria-hidden="true"></span>
                                    @if($event->reseller_id === null)
                                        <a href="{{ route('admin.invitations.edit', $event) }}" class="truncate underline-offset-4 hover:underline">{{ $event->title }}</a>
                                    @else
                                        <span class="truncate">{{ $event->title }}</span>
                                    @endif
                                    @if($event->event_date)
                                        <span class="shrink-0 text-xs text-site-muted">{{ $event->event_date->format('d/m/Y') }}</span>
                                    @endif
                                </p>
                            @empty
                                <p class="text-sm text-site-muted">Sin eventos a su nombre</p>
                            @endforelse
                        </div>

                        <div class="md:text-right">
                            <button type="button" class="admin-link-button" @click="regenerate()" :disabled="busy">
                                <x-phosphor-key aria-hidden="true" />
                                Nueva contraseña
                            </button>
                            <p x-show="password" x-cloak class="mt-2 text-sm">
                                Contraseña nueva: <span class="font-mono font-semibold" x-text="password"></span>
                            </p>
                        </div>
                    </li>
                @empty
                    <li class="flex flex-col items-center px-6 py-14 text-center">
                        <x-phosphor-users-light class="size-12 text-site-accent" aria-hidden="true" />
                        <p class="mt-4 font-medium">{{ $search !== '' ? 'Nadie coincide con «'.$search.'»' : 'Todavía no hay clientes aquí' }}</p>
                        <p class="mt-1 text-sm text-site-muted">Los clientes se crean desde el editor de cada invitación.</p>
                    </li>
                @endforelse
            </ul>
        </section>

        @include('layouts.partials.pagination', ['paginator' => $clients])
    </div>

    <script>
    // Contraseña nueva para un cliente que perdió la suya: la anterior deja de funcionar y esta se ve una vez
    function clientRow(url, name) {
        return {
            password: null,
            busy: false,
            async regenerate() {
                if (!confirm(`¿Generar una contraseña nueva para ${name}? La anterior deja de funcionar.`)) return;
                this.busy = true;
                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                    });
                    const data = await response.json();
                    this.password = data.client?.password ?? null;
                } finally {
                    this.busy = false;
                }
            },
        };
    }
    </script>
@endsection
