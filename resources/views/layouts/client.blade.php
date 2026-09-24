<!DOCTYPE html>
<html lang="es">
<head>
    @include('layouts.partials.panel-head')
    <title>@yield('title', 'Mis eventos') | {{ config('bida.brand') }}</title>
</head>
{{--
    Panel del cliente. El revendedor (Hazlo tú) maneja muchos eventos: tiene la barra lateral con su
    navegación y los filtros de sus invitaciones, igual que el administrador. El cliente de un evento
    ve una cabecera simple.
--}}
@php($panelUser = auth()->user())
@if($panelUser?->isReseller())
    <body class="site client-shell panel-layout min-h-[100dvh]" x-data="{ sideOpen: false }" @keydown.escape.window="sideOpen = false">
        @include('layouts.partials.panel-sidebar', [
            'homeUrl' => route('client.dashboard'),
            // Solo si puede crear hoy: suscripción al día y cupo del mes
            'primary' => $panelUser->can('create', App\Models\Invitation::class) && App\Support\ResellerSubscription::hasQuotaLeft($panelUser)
                ? ['label' => 'Nueva invitación', 'url' => route('client.invitations.create')]
                : null,
            'nav' => [
                ['label' => 'Mis eventos', 'url' => route('client.dashboard'), 'icon' => 'squares-four', 'active' => request()->routeIs('client.dashboard', 'client.invitation.show')],
                ['label' => 'Mi plan', 'url' => route('diy').'#planes', 'icon' => 'seal-check', 'active' => false],
                ['label' => 'Ver el sitio', 'url' => route('home'), 'icon' => 'arrow-square-out', 'active' => false],
            ],
            'filterGroups' => $filterGroups ?? null,
            'filterValues' => $filterValues ?? [],
            'filterRoute' => 'client.dashboard',
        ])

        <div class="panel-main">
            <header class="panel-top">
                <button type="button" class="admin-icon-button panel-top__menu" @click="sideOpen = true" aria-label="Abrir el menú">
                    <x-phosphor-list aria-hidden="true" />
                </button>
                <a href="{{ route('client.dashboard') }}" class="panel-top__logo"><x-brand.logo /></a>
                <div class="ml-auto flex items-center gap-1.5">
                    <span class="mr-1 hidden text-sm text-site-muted sm:inline">{{ $panelUser->business_name ?: $panelUser->name }}</span>
                    @include('layouts.partials.panel-actions')
                </div>
            </header>

            <main class="panel-content">
                @yield('content')
            </main>
        </div>

        @include('layouts.partials.copy-script')
    </body>
@else
    <body class="site client-shell min-h-[100dvh]">
        <header class="site-header is-scrolled sticky top-0 z-40">
            <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-5 lg:px-8">
                <a href="{{ route('client.dashboard') }}" class="shrink-0 text-lg">
                    <x-brand.logo />
                </a>
                <div class="flex items-center gap-1.5">
                    @if($panelUser?->is_admin)
                        <a href="{{ route('admin.dashboard') }}" class="admin-link-button mr-2">Panel admin</a>
                    @endif
                    <span class="mr-1 hidden text-sm text-site-muted sm:inline">{{ $panelUser?->name }}</span>
                    @include('layouts.partials.panel-actions')
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-5 py-8 lg:px-8 lg:py-12">
            @yield('content')
        </main>

        @include('layouts.partials.copy-script')
    </body>
@endif
</html>
