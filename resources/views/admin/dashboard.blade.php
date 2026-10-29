@extends('layouts.admin')

{{-- La misma lista sirve para las invitaciones de clientes y, aparte, para las de muestra del sitio --}}
@php($isShowcase ??= false)
@php($listRoute ??= 'admin.dashboard')

@section('title', $isShowcase ? 'Muestras' : 'Invitaciones')

{{--
    Inicio del administrador: el título con su «+», tres números del momento, lo que pide atención (con atajo) y los
    eventos en una sola lista por fecha, con el tipo como ícono y lo ya pasado al final. Filtros en la barra lateral.
    Estilos: admin.css («Inicio: mis eventos» y «Inicio del administrador»).
--}}
@section('content')
    <div class="mx-auto grid w-full max-w-[48rem] gap-5">
        <header class="site-enter flex items-center justify-between gap-4">
            <h1 class="site-display site-display--md">{{ $isShowcase ? 'Muestras' : 'Invitaciones' }}</h1>
            <a href="{{ route('admin.invitations.create') }}" class="ev-add" title="Nueva invitación" aria-label="Nueva invitación">
                <x-phosphor-plus-light aria-hidden="true" />
            </a>
        </header>

        {{-- Tres números sueltos: los eventos de la semana, lo que está en línea y lo cobrado en el mes --}}
        @if($pulse)
            <dl class="site-enter adm-pulse" style="--enter-index: 1">
                <div>
                    <dt><x-phosphor-calendar-check-light aria-hidden="true" /> Esta semana</dt>
                    <dd>{{ $pulse['weekEvents'] }} <small>{{ $pulse['weekEvents'] === 1 ? 'evento' : 'eventos' }}</small></dd>
                </div>
                <div>
                    <dt><x-phosphor-check-circle-light aria-hidden="true" /> Activas</dt>
                    <dd>{{ $pulse['active'] }} <small>de {{ $pulse['total'] }} · {{ $pulse['guests'] }} invitados</small></dd>
                </div>
                <div>
                    <dt><x-phosphor-coins-light aria-hidden="true" /> Este mes</dt>
                    <dd>
                        @forelse($pulse['revenue'] as $amount)
                            <span class="block">{{ $amount }}</span>
                        @empty
                            <span class="text-site-muted">—</span>
                        @endforelse
                    </dd>
                </div>
            </dl>
        @endif

        {{-- Lo que pide atención, solo si hay algo: revendedores por vencer y pagos que siguen esperando --}}
        @if($attention !== [])
            <section class="site-enter" style="--enter-index: 2" aria-labelledby="por-atender">
                <h2 id="por-atender" class="ev-label">Por atender</h2>
                <ul class="ev-attn">
                    @foreach($attention as $item)
                        <li>
                            <a href="{{ $item['url'] }}" class="ev-attn__row">
                                <x-dynamic-component :component="'phosphor-'.$item['icon'].'-light'" aria-hidden="true" />
                                <span>
                                    {{ $item['text'] }}
                                    <small @class(['block text-xs', 'text-site-danger' => $item['danger'], 'text-site-muted' => ! $item['danger']])>{{ $item['note'] }}</small>
                                </span>
                                <x-phosphor-caret-right-light aria-hidden="true" />
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        {{-- Buscar: en el celular, a la vista (la barra lateral queda en el menú); en computadora ya está en la barra --}}
        <form method="GET" action="{{ route($listRoute) }}" class="site-enter ev-search ev-search--mobile" style="--enter-index: 3" role="search">
            @foreach(collect($filterValues ?? [])->except('q')->filter() as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <label for="buscar-invitacion" class="sr-only">Buscar invitaciones</label>
            <x-phosphor-magnifying-glass-light class="ev-search__icon" aria-hidden="true" />
            <input id="buscar-invitacion" type="search" name="q" value="{{ $search }}" class="site-input" placeholder="Evento, enlace o cliente" autocomplete="off">
        </form>

        @if($isFiltered)
            <p class="site-enter flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-site-muted" style="--enter-index: 3">
                <span>
                    {{ $invitations->total() }} {{ $invitations->total() === 1 ? 'coincide' : 'coinciden' }}
                    @if($search !== '') con «{{ $search }}» @endif
                </span>
                <a href="{{ route($listRoute) }}" class="site-help"><x-phosphor-x-light aria-hidden="true" /> Quitar filtros</a>
            </p>
        @endif

        @if($items->isNotEmpty())
            <ul class="site-enter ev-list" style="--enter-index: 4">
                @php($pastShown = false)
                @foreach($items as $row)
                    {{-- Lo que ya pasó va al final, con un separador --}}
                    @if($row['isPast'] && ! $pastShown)
                        @php($pastShown = true)
                        <li class="ev-sep"><span class="ev-label"><x-phosphor-clock-counter-clockwise-light aria-hidden="true" /> Ya pasaron</span></li>
                    @endif
                    @include('admin.partials.invitation-row', ['row' => $row])
                @endforeach
            </ul>
        @else
            <div class="site-enter ev-empty" style="--enter-index: 3">
                <x-phosphor-envelope-simple-open-light class="ev-empty__icon" aria-hidden="true" />
                @if($isFiltered)
                    <h2 class="ev-empty__title">Nada coincide con esa búsqueda</h2>
                    <a href="{{ route($listRoute) }}" class="site-help"><x-phosphor-x-light aria-hidden="true" /> Quitar filtros</a>
                @elseif($isShowcase)
                    <h2 class="ev-empty__title">No hay invitaciones de muestra</h2>
                    <p class="ev-empty__text">Son las que dice config/bida.php (demo_invitations, temporadas, «Únete» y páginas por evento).</p>
                @else
                    <h2 class="ev-empty__title">Todavía no hay invitaciones</h2>
                    <a href="{{ route('admin.invitations.create') }}" class="site-btn site-btn--lg">
                        <x-phosphor-plus-light aria-hidden="true" />
                        Nueva invitación
                    </a>
                @endif
            </div>
        @endif

        @include('layouts.partials.pagination', ['paginator' => $invitations])
    </div>
@endsection
