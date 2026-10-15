@extends('layouts.client')

@section('title', 'Mis eventos')

@section('content')
    <header class="site-enter">
        <h1 class="text-3xl font-semibold tracking-tight md:text-4xl">Mis eventos</h1>
        <p class="mt-2 max-w-[52ch] text-site-muted">
            Abre tu página, comparte el enlace y revisa quién respondió.
        </p>
    </header>

    @include('client.partials.export-status')

    @if(session('success'))
        <p class="site-enter mt-6 flex items-center gap-2 rounded-[12px] border border-site-line bg-site-surface px-4 py-3 text-sm" role="status">
            <x-phosphor-check-circle class="size-5 shrink-0 text-site-accent" aria-hidden="true" />
            {{ session('success') }}
        </p>
    @endif

    @if($errors->has('quota'))
        <p class="site-enter mt-6 flex items-start gap-2 rounded-[12px] border border-site-danger/40 bg-site-danger/10 px-4 py-3 text-sm text-site-danger" role="alert">
            <x-phosphor-warning-circle class="size-5 shrink-0" aria-hidden="true" />
            {{ $errors->first('quota') }}
        </p>
    @endif

    {{-- Revendedor: su plan, el cupo del mes y el acceso al editor --}}
    @if($reseller)
        <section class="site-enter admin-card mt-8 p-6 lg:p-8" style="--enter-index: 1">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-site-muted">Plan {{ $reseller['planName'] }}</p>
                    <p class="mt-1 text-3xl font-semibold tracking-tight tabular-nums">{{ $reseller['quotaLabel'] }}</p>
                    <p class="mt-1 text-sm text-site-muted">
                        invitaciones publicadas este mes
                        @if($reseller['renewsLabel'])
                            · {{ match (true) { $reseller['isPastDue'] => 'venció el', $reseller['autoRenews'] => 'se renueva solo el', default => 'renueva el' } }} {{ $reseller['renewsLabel'] }}
                        @endif
                    </p>
                </div>

                <div class="sm:text-right">
                    @if($reseller['canCreate'])
                        <a href="{{ route('client.invitations.create') }}" class="admin-primary-button">
                            <x-phosphor-plus-bold aria-hidden="true" />
                            Nueva invitación
                        </a>
                    @else
                        <span class="admin-primary-button pointer-events-none opacity-50" aria-disabled="true">
                            <x-phosphor-plus-bold aria-hidden="true" />
                            Nueva invitación
                        </span>
                        <p class="mt-2 text-sm text-site-muted">{{ $reseller['blockedReason'] }}</p>
                    @endif
                    @if($reseller['quotaNote'])
                        <p class="mt-2 max-w-[34ch] text-sm text-site-muted sm:ml-auto">{{ $reseller['quotaNote'] }}</p>
                    @endif
                </div>
            </div>

            @if($reseller['warning'])
                <p @class([
                    'mt-5 flex items-start gap-2 rounded-[12px] px-4 py-3 text-sm',
                    'bg-site-danger/10 text-site-danger' => ! $reseller['isActive'],
                    'bg-site-tint' => $reseller['isActive'],
                ]) role="status">
                    <x-phosphor-bell-ringing class="size-5 shrink-0" aria-hidden="true" />
                    <span>
                        {{ $reseller['warning'] }}
                        @if($reseller['payUrl'])
                            <a href="{{ $reseller['payUrl'] }}" class="ml-1 font-medium underline underline-offset-4">{{ $reseller['isActive'] ? 'Renovar' : 'Pagar ahora' }}</a>
                        @endif
                    </span>
                </p>
            @endif
        </section>
    @endif

    {{-- «Hazlo tú»: cualquiera arma su invitación sin pagar antes; al publicar elige gratis o con más --}}
    @if($diy && $total > 0)
        <section class="site-enter admin-card mt-8 flex flex-col gap-4 p-6 sm:flex-row sm:items-center sm:justify-between lg:p-8" style="--enter-index: 1">
            <div>
                <h2 class="text-lg font-semibold tracking-tight">Arma otra invitación</h2>
                <p class="mt-1 max-w-[52ch] text-sm text-site-muted">
                    Pruébala con todas las partes que quieras: pagas solo si al publicarla usas más de lo gratuito.
                    @if($diy['drafts'] > 0)
                        Tienes {{ $diy['drafts'] }} de {{ $diy['limit'] }} borradores.
                    @endif
                </p>
            </div>
            <div class="sm:text-right">
                @if($diy['canCreate'])
                    <a href="{{ route('client.diy.create') }}" class="admin-primary-button">
                        <x-phosphor-plus-bold aria-hidden="true" />
                        Nueva invitación
                    </a>
                @else
                    <span class="admin-primary-button pointer-events-none opacity-50" aria-disabled="true">
                        <x-phosphor-plus-bold aria-hidden="true" />
                        Nueva invitación
                    </span>
                    <p class="mt-2 text-sm text-site-muted">{{ $diy['blockedReason'] }}</p>
                @endif
            </div>
        </section>
    @endif

    {{-- Buscador: viaja en la URL, así el cliente puede volver al mismo resultado --}}
    @if(empty($filterGroups) && ($total > 0 || $search !== ''))
        <form method="GET" class="site-enter mt-8 flex flex-wrap items-center gap-2" style="--enter-index: 1">
            <label for="buscar-evento" class="sr-only">Buscar entre mis eventos</label>
            <div class="relative min-w-[14rem] flex-1">
                <x-phosphor-magnifying-glass class="pointer-events-none absolute left-3 top-1/2 size-5 -translate-y-1/2 text-site-muted" aria-hidden="true" />
                <input id="buscar-evento" type="search" name="q" value="{{ $search }}" class="admin-input has-icon"
                    placeholder="Buscar por nombre del evento" autocomplete="off">
            </div>
            <button type="submit" class="admin-link-button">Buscar</button>
            @if($search !== '')
                <a href="{{ route('client.dashboard') }}" class="admin-link-button">Ver todos</a>
            @endif
        </form>
    @endif

    @forelse($sections as $sectionIndex => $section)
        <section class="site-enter mt-10" style="--enter-index: {{ $sectionIndex + 2 }}">
            <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 border-b border-site-line pb-3">
                <h2 class="text-xl font-semibold tracking-tight">{{ $section['title'] }}</h2>
                <p class="text-sm text-site-muted">{{ $section['description'] }}</p>
            </div>

            <div class="mt-5 grid gap-5">
                @foreach($section['rows'] as $row)
                    @php($invitation = $row['invitation'])
                    <article class="admin-card p-6 lg:p-8">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <p class="text-sm text-site-muted">{{ $row['typeLabel'] }} · {{ $row['templateLabel'] }}@if($row['ownerLabel']) · <span class="text-site-ink">{{ $row['ownerLabel'] }}</span>@endif</p>
                                <h3 class="mt-1 truncate text-2xl font-semibold tracking-tight">{{ $invitation->title }}</h3>
                                @if($invitation->event_date)
                                    <p class="mt-2 inline-flex items-center gap-2 text-site-muted">
                                        <x-phosphor-calendar-blank class="size-5 shrink-0" aria-hidden="true" />
                                        {{ $invitation->event_date->locale(app()->getLocale())->translatedFormat(\App\Support\Localization::dateFormat('j \d\e F \d\e Y')) }}, {{ $invitation->event_date->format('H:i') }}
                                    </p>
                                @endif
                            </div>
                            <div class="flex flex-col gap-1.5 self-start sm:items-end">
                                <span class="admin-status-badge {{ $row['isPast'] ? 'is-primary' : ($row['publicUrl'] ? 'is-success' : 'is-pending') }}">
                                    <span class="admin-status-dot"></span>
                                    {{ $row['isPast'] ? 'Ya celebrado' : ($row['publicUrl'] ? 'En línea' : ($invitation->is_diy && ! $row['tierLabel'] ? 'Borrador' : 'Sin publicar')) }}
                                </span>
                                {{-- «Hazlo tú»: el nivel con el que se publicó --}}
                                @if($row['tierLabel'])
                                    <span class="text-xs text-site-muted">Nivel {{ $row['tierLabel'] }}</span>
                                @endif
                            </div>
                        </div>

                        <dl @class([
                            'mt-7 grid grid-cols-2 border-y border-site-line py-5',
                            'sm:grid-cols-3' => count($row['metrics']) === 3,
                        ])>
                            @foreach($row['metrics'] as $metric)
                                <div class="border-site-line px-4 first:pl-0 [&:not(:first-child)]:border-l">
                                    <dt class="text-sm text-site-muted">{{ $metric['label'] }}</dt>
                                    <dd class="mt-1 text-3xl font-semibold tracking-tight tabular-nums">{{ $metric['value'] }}</dd>
                                    @if($metric['note'] ?? null)
                                        <p class="mt-0.5 text-xs text-site-muted">{{ $metric['note'] }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </dl>

                        <div class="mt-6 flex flex-wrap items-center gap-2">
                            {{-- El panel de invitados viene en el paquete Premium (App\Support\Packages) --}}
                            @can('view', $invitation)
                                <a href="{{ route('client.invitation.show', $invitation) }}" class="admin-primary-button">
                                    <x-dynamic-component :component="$row['isCard'] ? 'phosphor-chat-circle-text' : 'phosphor-list-checks'" aria-hidden="true" />
                                    {{ $row['isCard'] ? 'Ver respuestas' : 'Ver invitados' }}
                                </a>
                            @else
                                <p class="text-sm text-site-muted">La lista de invitados y las confirmaciones vienen en el paquete Premium.</p>
                            @endcan

                            {{-- Solo el revendedor al día edita lo suyo; al cliente normal se la arma el equipo --}}
                            @can('update', $invitation)
                                <a href="{{ route('client.invitations.edit', $invitation) }}" class="admin-link-button">
                                    <x-phosphor-pencil-simple aria-hidden="true" />
                                    Editar
                                </a>
                            @endcan

                            {{-- «Hazlo tú»: su dueño la edita siempre; un borrador que nunca se publicó, también lo borra --}}
                            @can('editOwn', $invitation)
                                <a href="{{ route('client.diy.edit', $invitation) }}" class="admin-link-button">
                                    <x-phosphor-pencil-simple aria-hidden="true" />
                                    Editar
                                </a>
                            @endcan

                            @if($row['publicUrl'])
                                <a href="{{ $row['publicUrl'] }}" target="_blank" rel="noopener" class="admin-link-button">
                                    <x-phosphor-arrow-square-out aria-hidden="true" />
                                    Abrir mi {{ mb_strtolower($row['kindLabel']) }}
                                </a>
                                <x-ui.copy-button :text="$row['publicUrl']" label="Copiar enlace" />
                            @else
                                <span class="inline-flex items-center gap-2 text-sm text-site-muted">
                                    <x-phosphor-info class="size-4" aria-hidden="true" />
                                    {{ $row['unavailableReason'] }}
                                </span>
                            @endif

                            @can('deleteOwn', $invitation)
                                <form method="POST" action="{{ route('client.diy.destroy', $invitation) }}" class="sm:ml-auto"
                                    onsubmit="return confirm(@js('¿Borrar el borrador «'.$invitation->title.'»? No se puede recuperar.'))">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="admin-link-button text-site-danger">
                                        <x-phosphor-trash aria-hidden="true" />
                                        Borrar borrador
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @empty
        <div class="site-enter admin-card mt-10 flex flex-col items-center px-6 py-16 text-center" style="--enter-index: 2">
            <x-phosphor-calendar-blank-light class="size-12 text-site-accent" aria-hidden="true" />
            @if($search !== '')
                <h2 class="mt-4 text-lg font-medium">Ningún evento se llama así</h2>
                <p class="mt-1 max-w-[40ch] text-site-muted">Prueba con otra palabra del nombre.</p>
                <a href="{{ route('client.dashboard') }}" class="admin-link-button mt-6">Ver todos mis eventos</a>
            @else
                @if($reseller)
                    {{-- Con plan: lo que sigue es crear la primera invitación --}}
                    <x-site.journey :current="4" class="mb-8 w-full max-w-md text-left" />
                    <h2 class="text-lg font-medium">Crea tu primera invitación</h2>
                    <p class="mt-1 max-w-[40ch] text-site-muted">Elige una plantilla, sube tus fotos y publícala. Aparecerá aquí con sus confirmaciones.</p>
                    @if($reseller['canCreate'])
                        <a href="{{ route('client.invitations.create') }}" class="admin-primary-button mt-6">
                            <x-phosphor-plus-bold aria-hidden="true" />
                            Nueva invitación
                        </a>
                    @endif
                @elseif($diy)
                    {{-- Cuenta recién abierta: arma tu invitación sin pagar antes («Hazlo tú»); al publicar eliges gratis o con más --}}
                    <h2 class="text-lg font-medium">Arma tu invitación, gratis</h2>
                    <p class="mt-1 max-w-[46ch] text-site-muted">
                        Elige un diseño y prueba todas las partes que quieras. Al publicarla decides: gratis con la portada,
                        la cuenta regresiva y la ubicación, o con todo lo que armaste.
                    </p>
                    <div class="mt-6 flex flex-wrap justify-center gap-2">
                        <a href="{{ route('client.diy.create') }}" class="admin-primary-button">
                            <x-phosphor-plus-bold aria-hidden="true" />
                            Crear mi invitación
                        </a>
                        <a href="{{ lroute('pricing') }}#te-la-hacemos" class="admin-link-button">Prefiero que me la hagan</a>
                    </div>
                    <p class="mt-6 max-w-[46ch] text-sm text-site-muted">
                        ¿Armas invitaciones para tus clientes? <a href="{{ $startUrl }}" class="font-medium text-site-ink underline underline-offset-4">Mira los planes mensuales</a>.
                        Si el equipo ya está armando tu invitación, aparecerá aquí cuando esté lista.
                    </p>
                @endif
            @endif
        </div>
    @endforelse
@endsection
