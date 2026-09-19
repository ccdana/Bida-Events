@extends('layouts.admin')

@section('title', 'Invitaciones')

@section('content')
    <div class="grid gap-10">
        <header class="site-enter">
            <h1 class="text-3xl font-semibold tracking-tight md:text-4xl">Invitaciones</h1>
            <p class="mt-2 max-w-[52ch] text-site-muted">Revisa el estado de cada evento y entra directo al editor.</p>
        </header>

        <dl class="site-enter grid grid-cols-2 gap-y-6 border-y border-site-line py-6 lg:grid-cols-4" style="--enter-index: 1">
            @foreach([
                ['label' => 'Invitaciones', 'key' => 'total', 'icon' => 'envelope-simple'],
                ['label' => 'Activas', 'key' => 'active', 'icon' => 'check-circle'],
                ['label' => 'Inactivas', 'key' => 'inactive', 'icon' => 'eye-slash'],
                ['label' => 'Invitados', 'key' => 'guests', 'icon' => 'users-three'],
            ] as $metric)
                <div class="lg:border-l lg:border-site-line lg:px-6 lg:first:border-l-0 lg:first:pl-0">
                    <dt class="admin-metric-label flex items-center gap-2">
                        <x-dynamic-component :component="'phosphor-'.$metric['icon']" class="size-4" aria-hidden="true" />
                        {{ $metric['label'] }}
                    </dt>
                    <dd class="admin-metric-value">{{ $metrics[$metric['key']] }}</dd>
                </div>
            @endforeach
        </dl>

        <section class="site-enter" style="--enter-index: 2">
            <div class="flex items-baseline justify-between gap-4">
                <h2 class="text-xl font-semibold tracking-tight">Invitaciones y tarjetas</h2>
                <p class="text-sm text-site-muted">{{ $invitations->total() }} {{ $kind ? 'en este filtro' : 'en total' }}</p>
            </div>

            <nav class="mt-4 flex flex-wrap gap-2" aria-label="Filtrar por tipo">
                @foreach($filters as $filter)
                    <a href="{{ $filter['url'] }}"
                       class="{{ $filter['kind'] === $kind ? 'admin-primary-button' : 'admin-link-button' }}"
                       @if($filter['kind'] === $kind) aria-current="page" @endif>
                        {{ $filter['label'] }}
                    </a>
                @endforeach
            </nav>

            <ul class="mt-5 divide-y divide-site-line overflow-hidden rounded-[16px] border border-site-line bg-site-surface">
                @forelse($items as $row)
                    @php($invitation = $row['invitation'])
                    <li class="adm-row flex flex-col gap-4 p-5 lg:flex-row lg:items-center lg:justify-between lg:px-6">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-3">
                                <h3 class="truncate text-lg font-medium">{{ $invitation->title }}</h3>
                                <span class="admin-status-badge {{ $row['statusClass'] }}">
                                    <span class="admin-status-dot"></span>
                                    {{ $row['statusLabel'] }}
                                </span>
                            </div>
                            <p class="mt-2 flex flex-wrap items-center gap-x-5 gap-y-1.5 text-sm text-site-muted">
                                <span class="inline-flex items-center gap-1.5">
                                    @if($row['isCard'])
                                        <x-phosphor-heart class="size-4" aria-hidden="true" />
                                        Tarjeta · {{ $row['eventTypeName'] }}
                                    @else
                                        <x-phosphor-confetti class="size-4" aria-hidden="true" />
                                        {{ $row['eventTypeName'] }}
                                    @endif
                                </span>
                                @if($row['eventDateLabel'])
                                    <span class="inline-flex items-center gap-1.5">
                                        <x-phosphor-calendar-blank class="size-4" aria-hidden="true" />
                                        {{ $row['eventDateLabel'] }}
                                    </span>
                                @endif
                                @unless($row['isCard'])
                                    <span class="inline-flex items-center gap-1.5">
                                        <x-phosphor-users class="size-4" aria-hidden="true" />
                                        {{ $row['guestCount'] }} invitados
                                    </span>
                                @endunless
                                <span class="font-mono text-xs">/p/{{ $invitation->slug }}</span>
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('invitation.show', $invitation->slug) }}" target="_blank" rel="noopener" class="admin-link-button">
                                <x-phosphor-arrow-square-out aria-hidden="true" />
                                Ver
                            </a>
                            <a href="{{ route('admin.guests.index', $invitation) }}" class="admin-link-button">
                                <x-phosphor-users aria-hidden="true" />
                                Invitados
                            </a>
                            <a href="{{ route('admin.invitations.edit', $invitation) }}" class="admin-primary-button">
                                <x-phosphor-pencil-simple aria-hidden="true" />
                                Editar
                            </a>
                        </div>
                    </li>
                @empty
                    <li class="flex flex-col items-center px-6 py-16 text-center">
                        <x-phosphor-envelope-simple-open-light class="size-12 text-site-accent" aria-hidden="true" />
                        <h3 class="mt-4 text-lg font-medium">{{ $kind === 'card' ? 'Todavía no hay tarjetas' : 'Todavía no hay invitaciones' }}</h3>
                        <p class="mt-1 max-w-[40ch] text-site-muted">Crea la primera, asígnale un cliente y compártela cuando esté lista.</p>
                        <a href="{{ route('admin.invitations.create') }}" class="admin-primary-button mt-6">
                            <x-phosphor-plus-bold aria-hidden="true" />
                            Nueva invitación
                        </a>
                    </li>
                @endforelse
            </ul>

            @include('layouts.partials.pagination', ['paginator' => $invitations])
        </section>
    </div>
@endsection
