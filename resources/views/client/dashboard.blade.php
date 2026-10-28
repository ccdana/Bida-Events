@extends('layouts.client')

@section('title', 'Mis eventos')

{{--
    Inicio de quien entra al panel del cliente: «Hazlo tú», cliente de un evento (se la armó el equipo) y revendedor.
    Una columna angosta: el título con su «+», lo que pide atención, el próximo evento destacado (cuenta regresiva y
    confirmaciones) y el resto en filas con filete; lo ya celebrado, plegado al final. Sin tarjetas ni párrafos.
    Estilos: admin.css («Inicio: mis eventos»).
--}}
@section('content')
    @php($addUrl = $reseller ? route('client.invitations.create') : ($diy ? route('client.diy.create') : null))
    @php($canAdd = $reseller ? $reseller['canCreate'] : ($diy['canCreate'] ?? false))
    @php($addReason = $reseller ? $reseller['blockedReason'] : ($diy['blockedReason'] ?? null))

    <div class="mx-auto grid w-full max-w-[45rem] gap-5">
        <header class="site-enter flex items-center justify-between gap-4">
            <h1 class="site-display site-display--md">Mis eventos</h1>
            {{-- Sin eventos, el botón grande de abajo ya invita a crear: el «+» sobra --}}
            @if($addUrl && $canAdd && ($total > 0 || $search !== ''))
                <a href="{{ $addUrl }}" class="ev-add" title="Nueva invitación" aria-label="Nueva invitación">
                    <x-phosphor-plus-light aria-hidden="true" />
                </a>
            @elseif($addUrl)
                <span class="ev-add is-off" role="img" aria-disabled="true" title="{{ $addReason }}" aria-label="Nueva invitación: {{ $addReason }}">
                    <x-phosphor-plus-light aria-hidden="true" />
                </span>
            @endif
        </header>

        @include('client.partials.export-status')

        @if(session('success'))
            <p class="site-enter flex items-start gap-2 border-l-2 border-site-accent py-1 pl-3 text-sm" role="status">
                <x-phosphor-check-circle-light class="mt-0.5 size-4 shrink-0 text-site-accent" aria-hidden="true" />
                {{ session('success') }}
            </p>
        @endif

        @if($errors->has('quota'))
            <p class="site-enter flex items-start gap-2 border-l-2 border-site-danger py-1 pl-3 text-sm text-site-danger" role="alert">
                <x-phosphor-warning-circle-light class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
                {{ $errors->first('quota') }}
            </p>
        @endif

        {{-- Revendedor: su plan, lo publicado este mes con una línea fina y cuándo se renueva --}}
        @if($reseller)
            <section class="site-enter ev-plan" style="--enter-index: 1" aria-label="Tu plan">
                <p class="ev-plan__head">
                    <span class="ev-label"><x-phosphor-storefront-light aria-hidden="true" /> Plan {{ $reseller['planName'] }}</span>
                    @if($reseller['renewsLabel'])
                        <span class="ev-plan__renew">
                            {{ match (true) { $reseller['isPastDue'] => 'venció el', $reseller['autoRenews'] => 'se renueva solo el', default => 'renueva el' } }} {{ $reseller['renewsShort'] }}
                        </span>
                    @endif
                </p>
                <p class="ev-plan__count">
                    <b>{{ $reseller['quotaLabel'] }}</b>
                    <span>invitaciones publicadas este mes</span>
                </p>
                @if($reseller['quotaPercent'] !== null)
                    <div class="ev-bar" role="img" aria-label="{{ $reseller['quotaPercent'] }} % del cupo del mes"><i style="width: {{ $reseller['quotaPercent'] }}%"></i></div>
                @endif

                @if($reseller['warning'])
                    <p @class(['ev-note mt-3', 'is-danger' => ! $reseller['isActive']]) role="status">
                        <x-phosphor-bell-ringing-light aria-hidden="true" />
                        <span>
                            {{ $reseller['warning'] }}
                            @if($reseller['payUrl'])
                                <a href="{{ $reseller['payUrl'] }}" class="ml-1 font-medium underline underline-offset-4">{{ $reseller['isActive'] ? 'Renovar' : 'Pagar ahora' }}</a>
                            @endif
                        </span>
                    </p>
                @elseif($reseller['quotaNote'])
                    <p class="ev-note mt-3"><x-phosphor-info-light aria-hidden="true" /> <span>{{ $reseller['quotaNote'] }}</span></p>
                @endif
            </section>
        @endif

        {{-- Lo que pide atención (revendedor): sin cliente o sin publicar con la fecha cerca, con atajo al editor --}}
        @if($attention !== [])
            <section class="site-enter" style="--enter-index: 2" aria-labelledby="por-atender">
                <h2 id="por-atender" class="ev-label">Por atender</h2>
                <ul class="ev-attn">
                    @foreach($attention as $item)
                        <li>
                            <a href="{{ $item['url'] }}" class="ev-attn__row">
                                <x-dynamic-component :component="'phosphor-'.$item['icon'].'-light'" aria-hidden="true" />
                                <span>{{ $item['text'] }}</span>
                                <x-phosphor-caret-right-light aria-hidden="true" />
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        {{-- El próximo evento, más grande: cuándo es, cómo van las confirmaciones y lo que se hace con él --}}
        @if($featured)
            @php($row = $featured)
            @php($invitation = $row['invitation'])
            <article class="site-enter ev-next" style="--enter-index: 3" aria-label="Próximo evento">
                <p class="ev-label">
                    <span class="ev-dot is-{{ $row['publicUrl'] ? 'live' : 'draft' }}" aria-hidden="true"></span>
                    Próximo · {{ $row['whenLabel'] }}
                    <span class="sr-only">{{ $row['publicUrl'] ? 'En línea' : ($row['isDraft'] ? 'Borrador' : 'Sin publicar') }}</span>
                </p>
                <h2 class="ev-next__name">{{ $invitation->title }}</h2>
                <p class="ev-meta">
                    @if($row['longDateLabel'])<span><x-phosphor-calendar-blank-light aria-hidden="true" /> {{ $row['longDateLabel'] }}</span>@endif
                    <span>{{ $row['typeLabel'] }}</span>
                    @if($row['ownerLabel'])<span class="text-site-ink">{{ $row['ownerLabel'] }}</span>@endif
                    @if($row['tierLabel'])<span>Nivel {{ $row['tierLabel'] }}</span>@endif
                </p>

                @unless($row['isCard'])
                    @if($row['total'] > 0)
                        <div class="ev-bar mt-4" role="img" aria-label="{{ $row['confirmed'] }} de {{ $row['total'] }} invitados confirmaron"><i style="width: {{ $row['progress'] }}%"></i></div>
                        <p class="ev-next__count">
                            <span><b>{{ $row['confirmed'] }}</b> confirmaron</span>
                            <span><b>{{ $row['pending'] }}</b> sin responder</span>
                            @if($row['declined'] > 0)<span><b>{{ $row['declined'] }}</b> no asisten</span>@endif
                        </p>
                    @endif
                @else
                    @include('client.partials.event-numbers', ['row' => $row])
                @endunless

                {{-- Lo que cambió en la última semana (se calcula con las fechas; no se guarda nada) --}}
                @if($row['newConfirmed'] > 0 || $row['newContributions'] > 0)
                    <p class="ev-news">
                        <x-phosphor-sparkle-light aria-hidden="true" />
                        <span>
                            @if($row['newConfirmed'] > 0)+{{ $row['newConfirmed'] }} {{ $row['newConfirmed'] === 1 ? 'confirmación' : 'confirmaciones' }} esta semana @endif
                            @if($row['newConfirmed'] > 0 && $row['newContributions'] > 0) · @endif
                            @if($row['newContributions'] > 0)+{{ $row['newContributions'] }} {{ $row['newContributions'] === 1 ? 'foto o canción' : 'fotos o canciones' }}@endif
                        </span>
                    </p>
                @endif

                @if($row['isDraft'])
                    <p class="ev-hint">
                        <x-phosphor-pencil-simple-line-light aria-hidden="true" />
                        Borrador{{ $row['draftLeft'] ? ' · '.$row['draftLeft'].($row['draftLeft'] === 1 ? ' cosa de ejemplo por cambiar' : ' cosas de ejemplo por cambiar') : '' }}
                    </p>
                @elseif(! $row['publicUrl'])
                    <p class="ev-hint"><x-phosphor-info-light aria-hidden="true" /> {{ $row['unavailableReason'] }}</p>
                @endif

                {{-- El día del evento (o dos antes): el control de entrada, a un toque --}}
                @if(! $row['isCard'] && $row['daysLeft'] !== null && $row['daysLeft'] >= 0 && $row['daysLeft'] <= 2)
                    @can('manageOwnGuests', $invitation)
                        @if(\App\Support\Packages::allowsFor($invitation, 'door'))
                            <a href="{{ route('client.invitation.show', $invitation) }}#control-entrada" class="ev-door">
                                <x-phosphor-door-open-light aria-hidden="true" />
                                <span>Abrir el control de entrada</span>
                                <x-phosphor-caret-right-light aria-hidden="true" />
                            </a>
                        @endif
                    @endcan
                @endif

                @include('client.partials.event-actions', ['row' => $row, 'primary' => true])
            </article>
        @endif

        {{-- Buscador: viaja en la URL, así el cliente puede volver al mismo resultado --}}
        @if(empty($filterGroups) && ($total > 6 || $search !== ''))
            <form method="GET" class="site-enter ev-search" style="--enter-index: 4">
                <label for="buscar-evento" class="sr-only">Buscar entre mis eventos</label>
                <x-phosphor-magnifying-glass-light class="ev-search__icon" aria-hidden="true" />
                <input id="buscar-evento" type="search" name="q" value="{{ $search }}" class="site-input" placeholder="Buscar por nombre del evento" autocomplete="off">
                @if($search !== '')
                    <a href="{{ route('client.dashboard') }}" class="ev-act" title="Ver todos" aria-label="Ver todos"><x-phosphor-x-light aria-hidden="true" /></a>
                @endif
            </form>
        @endif

        @if($list->isNotEmpty())
            <ul class="site-enter ev-list" style="--enter-index: 4">
                @foreach($list as $row)
                    @include('client.partials.event-row', ['row' => $row])
                @endforeach
            </ul>
        @endif

        {{-- Lo ya celebrado queda plegado al final --}}
        @if($past->isNotEmpty())
            <details class="site-enter ev-past" style="--enter-index: 5" @if($search !== '' || ($featured === null && $list->isEmpty())) open @endif>
                <summary class="ev-past__summary">
                    <span class="ev-label"><x-phosphor-clock-counter-clockwise-light aria-hidden="true" /> Ya celebrados · {{ $past->count() }}</span>
                    <x-phosphor-caret-down-light class="ev-past__caret" aria-hidden="true" />
                </summary>
                <ul class="ev-list">
                    @foreach($past as $row)
                        @include('client.partials.event-row', ['row' => $row])
                    @endforeach
                </ul>
            </details>
        @endif

        @if($total === 0)
            <div class="site-enter ev-empty" style="--enter-index: 2">
                @if($search !== '')
                    <x-phosphor-magnifying-glass-light class="ev-empty__icon" aria-hidden="true" />
                    <h2 class="ev-empty__title">Ningún evento se llama así</h2>
                    <a href="{{ route('client.dashboard') }}" class="site-help"><x-phosphor-arrow-left-light aria-hidden="true" /> Ver todos mis eventos</a>
                @elseif($reseller)
                    {{-- Con plan: lo que sigue es crear la primera invitación --}}
                    <x-phosphor-calendar-blank-light class="ev-empty__icon" aria-hidden="true" />
                    <h2 class="ev-empty__title">Crea tu primera invitación</h2>
                    @if($reseller['canCreate'])
                        <a href="{{ route('client.invitations.create') }}" class="site-btn site-btn--lg">
                            <x-phosphor-plus-light aria-hidden="true" />
                            Nueva invitación
                        </a>
                    @endif
                @elseif($diy)
                    {{-- Cuenta recién abierta: arma tu invitación sin pagar antes; al publicarla eliges gratis o con más --}}
                    <x-phosphor-calendar-blank-light class="ev-empty__icon" aria-hidden="true" />
                    <h2 class="ev-empty__title">Arma tu invitación, gratis</h2>
                    <p class="ev-empty__text">Elige un diseño y pruébala con todo. Al publicarla decides: gratis o con todo lo que armaste.</p>
                    <div class="flex flex-wrap items-center justify-center gap-3">
                        <a href="{{ route('client.diy.create') }}" class="site-btn site-btn--lg">
                            <x-phosphor-plus-light aria-hidden="true" />
                            Crear mi invitación
                        </a>
                        <a href="{{ lroute('pricing') }}#a-medida" class="site-help"><x-phosphor-chat-circle-dots-light aria-hidden="true" /> Quiero algo a medida</a>
                    </div>
                    <p class="ev-empty__text">
                        ¿Armas invitaciones para tus clientes?
                        <a href="{{ $startUrl }}" class="font-medium text-site-ink underline underline-offset-4">Mira los planes mensuales</a>.
                        Si el equipo ya está armando tu invitación, aparecerá aquí cuando esté lista.
                    </p>
                @endif
            </div>
        @endif
    </div>
@endsection
