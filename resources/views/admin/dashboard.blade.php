@extends('layouts.admin')

{{-- La misma lista sirve para las invitaciones de clientes y, aparte, para las de muestra del sitio --}}
@php($isShowcase ??= false)
@php($listRoute ??= 'admin.dashboard')

@section('title', $isShowcase ? 'Muestras' : 'Invitaciones')

@section('content')
    <div class="grid gap-10">
        <header class="site-enter">
            <h1 class="text-3xl font-semibold tracking-tight md:text-4xl">{{ $isShowcase ? 'Muestras' : 'Invitaciones' }}</h1>
            <p class="mt-2 max-w-[56ch] text-site-muted">
                @if($isShowcase)
                    Las invitaciones que el sitio enseña para probar: la portada, las temporadas, «Hazlo tú» y las páginas
                    por evento. Se editan como cualquier otra, pero no se mezclan con las de tus clientes.
                @else
                    Los eventos de tus clientes separados por tipo. Filtra desde la barra lateral y abre la ficha de cualquiera
                    para ver su cliente, sus enlaces y cómo van las confirmaciones sin entrar al editor. Las de muestra están en «Muestras».
                @endif
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

        @if($isFiltered)
            <p class="site-enter text-sm text-site-muted" style="--enter-index: 2">
                {{ $invitations->total() }} {{ $invitations->total() === 1 ? 'invitación coincide' : 'invitaciones coinciden' }} con los filtros
                @if($search !== '') y la búsqueda «{{ $search }}» @endif.
                <a href="{{ route($listRoute) }}" class="font-medium text-site-ink underline underline-offset-4">Ver todo</a>
            </p>
        @endif

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
                @if($isFiltered)
                    <h2 class="mt-4 text-lg font-medium">Nada coincide con esa búsqueda</h2>
                    <p class="mt-1 max-w-[40ch] text-site-muted">Prueba con otro nombre, o quita el filtro de tipo de evento.</p>
                    <a href="{{ route($listRoute) }}" class="admin-link-button mt-6">Ver todo</a>
                @elseif($isShowcase)
                    <h2 class="mt-4 text-lg font-medium">No hay invitaciones de muestra</h2>
                    <p class="mt-1 max-w-[44ch] text-site-muted">Son las que dice config/bida.php (demo_invitations, temporadas, «Hazlo tú» y páginas por evento).</p>
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
