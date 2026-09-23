@extends('layouts.admin')

@section('title', 'Invitaciones')

@section('content')
    <div class="grid gap-10">
        <header class="site-enter">
            <h1 class="text-3xl font-semibold tracking-tight md:text-4xl">Invitaciones</h1>
            <p class="mt-2 max-w-[56ch] text-site-muted">
                Todos los eventos separados por tipo. Abre la ficha de cualquiera para ver su cliente,
                sus enlaces y cómo van las confirmaciones sin entrar al editor.
            </p>
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

        <div class="site-enter grid gap-4" style="--enter-index: 2">
            {{-- Buscar por nombre del evento, enlace o cliente; viaja en la URL con el filtro --}}
            <form method="GET" class="flex flex-wrap items-center gap-2">
                @if($type !== '')
                    <input type="hidden" name="tipo" value="{{ $type }}">
                @endif
                <label for="buscar-evento" class="sr-only">Buscar evento o cliente</label>
                <div class="relative min-w-[16rem] flex-1">
                    <x-phosphor-magnifying-glass class="pointer-events-none absolute left-3 top-1/2 size-5 -translate-y-1/2 text-site-muted" aria-hidden="true" />
                    <input id="buscar-evento" type="search" name="q" value="{{ $search }}" autocomplete="off"
                        class="admin-input has-icon" placeholder="Buscar por evento, enlace o cliente">
                </div>
                <button type="submit" class="admin-link-button">Buscar</button>
                @if($search !== '')
                    <a href="{{ route('admin.dashboard', array_filter(['tipo' => $type])) }}" class="admin-link-button">Limpiar</a>
                @endif
            </form>

            <nav class="flex flex-wrap gap-2" aria-label="Filtrar por tipo de evento">
                @foreach($filters as $filter)
                    <a href="{{ $filter['url'] }}"
                       class="{{ $filter['slug'] === $type ? 'admin-primary-button' : 'admin-link-button' }}"
                       @if($filter['slug'] === $type) aria-current="page" @endif>
                        {{ $filter['label'] }}
                        <span class="tabular-nums opacity-70">{{ $filter['total'] }}</span>
                    </a>
                @endforeach
            </nav>

            @if($search !== '')
                <p class="text-sm text-site-muted">
                    {{ $invitations->total() }} {{ $invitations->total() === 1 ? 'resultado' : 'resultados' }} para «{{ $search }}»
                </p>
            @endif
        </div>

        @forelse($sections as $index => $section)
            <section class="site-enter" style="--enter-index: {{ $index + 3 }}">
                <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 border-b border-site-line pb-3">
                    <h2 class="flex items-center gap-2 text-xl font-semibold tracking-tight">
                        <x-dynamic-component :component="$section['isCard'] ? 'phosphor-heart' : 'phosphor-confetti'" class="size-5 text-site-accent" aria-hidden="true" />
                        {{ $section['name'] }}
                    </h2>
                    <p class="text-sm text-site-muted">
                        {{ $section['rows']->count() }} {{ $section['rows']->count() === 1 ? 'evento' : 'eventos' }} en esta página
                        @if($section['total'] > $section['rows']->count())
                            · {{ $section['total'] }} en total
                        @endif
                    </p>
                </div>

                <ul class="mt-4 grid gap-3">
                    @foreach($section['rows'] as $row)
                        @include('admin.partials.invitation-row', ['row' => $row])
                    @endforeach
                </ul>
            </section>
        @empty
            <div class="site-enter admin-card flex flex-col items-center px-6 py-16 text-center" style="--enter-index: 3">
                <x-phosphor-envelope-simple-open-light class="size-12 text-site-accent" aria-hidden="true" />
                @if($search !== '' || $type !== '')
                    <h2 class="mt-4 text-lg font-medium">Nada coincide con esa búsqueda</h2>
                    <p class="mt-1 max-w-[40ch] text-site-muted">Prueba con otro nombre, o quita el filtro de tipo de evento.</p>
                    <a href="{{ route('admin.dashboard') }}" class="admin-link-button mt-6">Ver todo</a>
                @else
                    <h2 class="mt-4 text-lg font-medium">Todavía no hay invitaciones</h2>
                    <p class="mt-1 max-w-[40ch] text-site-muted">Crea la primera, asígnale un cliente y compártela cuando esté lista.</p>
                    <a href="{{ route('admin.invitations.create') }}" class="admin-primary-button mt-6">
                        <x-phosphor-plus-bold aria-hidden="true" />
                        Nueva invitación
                    </a>
                @endif
            </div>
        @endforelse

        @include('layouts.partials.pagination', ['paginator' => $invitations])
    </div>
@endsection
