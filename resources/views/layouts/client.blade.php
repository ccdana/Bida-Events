<!DOCTYPE html>
<html lang="es">
<head>
    @include('layouts.partials.panel-head')
    <title>@yield('title', 'Mis eventos') | {{ config('bida.brand') }}</title>
</head>
<body class="site client-shell min-h-[100dvh]">
    <header class="site-header is-scrolled sticky top-0 z-40">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-5 lg:px-8">
            <a href="{{ route('client.dashboard') }}" class="shrink-0 text-lg">
                <x-brand.logo />
            </a>
            <div class="flex items-center gap-1.5">
                @if(auth()->user()?->is_admin)
                    <a href="{{ route('admin.dashboard') }}" class="admin-link-button mr-2">Panel admin</a>
                @endif
                <span class="mr-1 hidden text-sm text-site-muted sm:inline">{{ auth()->user()?->name }}</span>
                @include('layouts.partials.panel-actions')
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-5 py-8 lg:px-8 lg:py-12">
        @yield('content')
    </main>

    @include('layouts.partials.copy-script')
</body>
</html>
