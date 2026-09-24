<!DOCTYPE html>
<html lang="es">
<head>
    @include('layouts.partials.panel-head')
    <title>@yield('title', 'Panel') | {{ config('bida.brand') }}</title>
</head>
<body class="site admin-shell min-h-[100dvh]">
    <header class="site-header is-scrolled sticky top-0 z-40">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-5 lg:px-8">
            <div class="flex min-w-0 items-center gap-8">
                <a href="{{ route('admin.dashboard') }}" class="shrink-0 text-lg">
                    <x-brand.logo />
                </a>
                <nav class="hidden items-center gap-6 text-[0.95rem] md:flex" aria-label="Panel">
                    <a href="{{ route('admin.dashboard') }}" @class([
                        'site-nav-link',
                        'font-medium text-site-ink' => request()->routeIs('admin.dashboard'),
                        'text-site-muted hover:text-site-ink' => ! request()->routeIs('admin.dashboard'),
                    ])>Invitaciones</a>
                    @php($resellersDue = App\Support\ResellerSubscription::dueSoonCount())
                    <a href="{{ route('admin.resellers.index') }}" @class([
                        'site-nav-link inline-flex items-center gap-1.5',
                        'font-medium text-site-ink' => request()->routeIs('admin.resellers.*'),
                        'text-site-muted hover:text-site-ink' => ! request()->routeIs('admin.resellers.*'),
                    ])>
                        Revendedores
                        @if($resellersDue > 0)
                            {{-- Cuántos hay que avisar: vencen en los próximos días o ya vencieron --}}
                            <span class="rounded-full bg-site-accent px-1.5 text-xs font-semibold text-site-bg tabular-nums"
                                title="{{ $resellersDue }} por vencer o vencidos">{{ $resellersDue }}</span>
                        @endif
                    </a>
                    <a href="{{ route('admin.settings') }}" @class([
                        'site-nav-link',
                        'font-medium text-site-ink' => request()->routeIs('admin.settings'),
                        'text-site-muted hover:text-site-ink' => ! request()->routeIs('admin.settings'),
                    ])>Ajustes</a>
                </nav>
            </div>

            <div class="flex items-center gap-1.5">
                <a href="{{ route('admin.invitations.create') }}" class="admin-primary-button mr-2">
                    <x-phosphor-plus-bold aria-hidden="true" />
                    <span class="sr-only sm:not-sr-only">Nueva invitación</span>
                </a>
                <span class="mr-1 hidden text-sm text-site-muted lg:inline">{{ auth()->user()->name }}</span>
                @include('layouts.partials.panel-actions')
            </div>
        </div>
    </header>

    @if(session('success'))
        <div class="mx-auto max-w-7xl px-5 pt-6 lg:px-8">
            <p class="adm-flash site-enter" role="status">
                <x-phosphor-check-circle class="size-5 shrink-0" aria-hidden="true" />
                {{ session('success') }}
            </p>
        </div>
    @endif

    <main class="mx-auto max-w-7xl px-5 py-8 lg:px-8 lg:py-12">
        @yield('content')
    </main>

    @include('layouts.partials.copy-script')
</body>
</html>
