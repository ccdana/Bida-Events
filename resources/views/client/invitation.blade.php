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
                <p class="text-sm text-site-muted">{{ $typeLabel }} · {{ $templateLabel }}</p>
                <h1 class="mt-1 truncate text-3xl font-semibold tracking-tight md:text-4xl">{{ $invitation->title }}</h1>
                @if($invitation->event_date)
                    <p class="mt-2 inline-flex items-center gap-2 text-site-muted">
                        <x-phosphor-calendar-blank class="size-5 shrink-0" aria-hidden="true" />
                        {{ $invitation->event_date->locale('es')->translatedFormat('j \d\e F \d\e Y') }}, {{ $invitation->event_date->format('H:i') }}
                    </p>
                @endif
            </div>
        </div>

        {{-- Lo primero que el cliente busca aquí: abrir su página y mandarla --}}
        <div class="mt-5 flex flex-wrap items-center gap-2">
            @if($publicUrl)
                <a href="{{ $publicUrl }}" target="_blank" rel="noopener" class="admin-primary-button">
                    <x-phosphor-arrow-square-out aria-hidden="true" />
                    Abrir mi {{ mb_strtolower($kindLabel) }}
                </a>
                <x-ui.copy-button :text="$publicUrl" label="Copiar enlace" />
                @unless($isCard)
                    {{-- La misma invitación, pero se abre directo como historias de Instagram --}}
                    <x-ui.copy-button :text="$publicUrl.'?historias'" label="Copiar enlace en historias" />
                @endunless
            @else
                <span class="inline-flex items-center gap-2 text-sm text-site-muted">
                    <x-phosphor-info class="size-4" aria-hidden="true" />
                    {{ $unavailableReason }}
                </span>
            @endif
            @can('update', $invitation)
                @unless(auth()->user()->isAdmin())
                    <a href="{{ route('client.invitations.edit', $invitation) }}" class="admin-link-button">
                        <x-phosphor-pencil-simple aria-hidden="true" />
                        Editar
                    </a>
                @endunless
            @endcan
        </div>
    </header>

    @if(session('success'))
        <p class="site-enter mt-6 flex items-center gap-2 rounded-[12px] border border-site-line bg-site-surface px-4 py-3 text-sm">
            <x-phosphor-check-circle class="size-5 text-site-accent" aria-hidden="true" />
            {{ session('success') }}
        </p>
    @endif

    {{-- Al agregar un invitado, lo útil es su enlace personal: se copia de una vez --}}
    @if(session('guest'))
        <div class="site-enter mt-6 rounded-[12px] border border-site-line bg-site-surface p-4">
            <p class="flex items-center gap-2 font-medium">
                <x-phosphor-check-circle class="size-5 text-site-accent" aria-hidden="true" />
                {{ session('guest')['name'] }} ya está en tu lista
            </p>
            <p class="mt-1 text-sm text-site-muted">Este es su enlace personal: al abrirlo verá su nombre y podrá confirmar.</p>
            <div class="mt-3 flex flex-wrap items-center gap-2">
                <code class="truncate rounded-[8px] bg-site-tint px-2.5 py-1.5 font-mono text-xs">{{ session('guest')['link'] }}</code>
                <x-ui.copy-button :text="session('guest')['link']" label="Copiar su enlace" />
                <a href="https://wa.me/?text={{ rawurlencode(session('guest')['link']) }}" target="_blank" rel="noopener" class="admin-link-button">
                    <x-phosphor-whatsapp-logo aria-hidden="true" />
                    Enviar por WhatsApp
                </a>
            </div>
        </div>
    @endif

    @include('client.partials.export-status')

    {{-- Lo que más se busca, arriba: descargar la lista y la invitación, y el enlace de la puerta --}}
    @can('export', $invitation)
        <section class="site-enter mt-8 rounded-[16px] border border-site-line bg-site-surface p-5" style="--enter-index: 1" aria-labelledby="descargar">
            <h2 id="descargar" class="text-lg font-semibold tracking-tight">Descargar</h2>
            <p class="mt-1 text-sm text-site-muted">
                {{ $isCard ? 'Guarda tu tarjeta lista para imprimir.' : 'La lista de invitados para el salón y el catering, y tu invitación lista para imprimir.' }}
            </p>
            <div class="mt-4 flex flex-wrap gap-2">
                @include('client.partials.export-buttons', ['invitation' => $invitation, 'isCard' => $isCard])
            </div>
        </section>
    @endcan

    @unless($isCard)
        @can('manageOwnGuests', $invitation)
            @if(\App\Support\Packages::allows($invitation->package, 'door'))
                @include('client.partials.door', ['doorStats' => \App\Http\Controllers\Public\DoorController::stats($invitation)])
            @endif
        @endcan
    @endunless

    {{-- El revendedor que armó el evento maneja el acceso de su cliente --}}
    @can('update', $invitation)
        @unless(auth()->user()->isAdmin())
            @include('client.partials.event-client')
        @endunless
    @endcan

    @unless($isCard)
        <section class="site-enter mt-10" style="--enter-index: 2">
            <h2 class="border-b border-site-line pb-3 text-xl font-semibold tracking-tight">Cómo va tu evento</h2>
            <dl class="grid grid-cols-2 gap-y-6 border-b border-site-line py-6 lg:grid-cols-4">
                @foreach([
                    'Personas confirmadas' => $totalPasses,
                    'Invitados que confirmaron' => $confirmed->count(),
                    'Sin responder' => $pending->count(),
                    'Pases usados' => $confirmationRate.'%',
                ] as $label => $value)
                    <div class="lg:border-l lg:border-site-line lg:px-6 lg:first:border-l-0 lg:first:pl-0">
                        <dt class="admin-metric-label">{{ $label }}</dt>
                        <dd class="admin-metric-value">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>

        @include('client.partials.guest-list')
    @endunless

    @if($isCard || $replies->isNotEmpty())
        @include('client.partials.replies')
    @endif

    @if($photos->isNotEmpty())
        @include('client.partials.contribution-photos')
    @endif

    @if($songs->isNotEmpty())
        @include('client.partials.contribution-songs')
    @endif
@endsection
