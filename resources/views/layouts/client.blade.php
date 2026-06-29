<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Mi Evento') - Bida Events</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen client-shell antialiased transition-colors">
    <nav class="client-header">
        <div class="mx-auto max-w-5xl px-4 py-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <div class="client-brand-mark">BE</div>
                <div>
                    <a href="{{ route('client.dashboard') }}" class="client-brand-title block">
                        <span>Bida</span>Events
                    </a>
                    <p class="client-kicker">Panel del cliente</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ auth()->user()?->is_admin ? route('admin.dashboard') : '#' }}"
                   @if(!auth()->user()?->is_admin) style="display: none;" @endif
                   class="text-sm font-medium text-stone-600 hover:text-stone-900 transition">
                    Panel Admin
                </a>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-sm font-medium text-stone-600 hover:text-stone-900 transition">
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </div>
    </nav>
    <main class="client-shell-main mx-auto max-w-5xl px-4 py-8">
        @yield('content')
    </main>
</body>
</html>
