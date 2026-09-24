<!DOCTYPE html>
<html lang="es">
<head>
    @include('layouts.partials.panel-head')
    <title>@yield('title', 'Panel') | {{ config('bida.brand') }}</title>
</head>
{{--
    Panel del administrador: barra lateral con la navegación y, en la lista de invitaciones, los
    filtros (layouts/partials/panel-sidebar). La página que filtra manda $filterGroups y $filterValues.
--}}
<body class="site admin-shell panel-layout min-h-[100dvh]" x-data="{ sideOpen: false }" @keydown.escape.window="sideOpen = false">
    @php($resellersDue = App\Support\ResellerSubscription::dueSoonCount())
    @include('layouts.partials.panel-sidebar', [
        'homeUrl' => route('admin.dashboard'),
        'primary' => ['label' => 'Nueva invitación', 'url' => route('admin.invitations.create')],
        'nav' => [
            ['label' => 'Invitaciones', 'url' => route('admin.dashboard'), 'icon' => 'envelope-simple', 'active' => request()->routeIs('admin.dashboard')],
            ['label' => 'Revendedores', 'url' => route('admin.resellers.index'), 'icon' => 'storefront', 'active' => request()->routeIs('admin.resellers.*'),
                'badge' => $resellersDue ?: null, 'badgeTitle' => $resellersDue.' por vencer o vencidos'],
            ['label' => 'Ajustes', 'url' => route('admin.settings'), 'icon' => 'sliders-horizontal', 'active' => request()->routeIs('admin.settings')],
            ['label' => 'Ver el sitio', 'url' => route('home'), 'icon' => 'arrow-square-out', 'active' => false],
        ],
        'filterGroups' => $filterGroups ?? null,
        'filterValues' => $filterValues ?? [],
        'filterRoute' => 'admin.dashboard',
    ])

    <div class="panel-main">
        <header class="panel-top">
            <button type="button" class="admin-icon-button panel-top__menu" @click="sideOpen = true" aria-label="Abrir el menú">
                <x-phosphor-list aria-hidden="true" />
            </button>
            <a href="{{ route('admin.dashboard') }}" class="panel-top__logo"><x-brand.logo /></a>
            <div class="ml-auto flex items-center gap-1.5">
                <span class="mr-1 hidden text-sm text-site-muted lg:inline">{{ auth()->user()->name }}</span>
                @include('layouts.partials.panel-actions')
            </div>
        </header>

        @if(session('success'))
            <div class="panel-content pb-0">
                <p class="adm-flash site-enter" role="status">
                    <x-phosphor-check-circle class="size-5 shrink-0" aria-hidden="true" />
                    {{ session('success') }}
                </p>
            </div>
        @endif

        <main class="panel-content">
            @yield('content')
        </main>
    </div>

    @include('layouts.partials.copy-script')
</body>
</html>
