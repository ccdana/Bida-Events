<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Mi Evento') — Bida Events</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-50 text-stone-800 antialiased">
    <nav class="border-b border-stone-200 bg-white sticky top-0 z-50">
        <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ route('client.dashboard') }}" class="font-serif text-xl text-stone-900">Mi <span class="text-amber-700">Evento</span></a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm text-stone-600 hover:text-stone-900">Cerrar sesión</button>
            </form>
        </div>
    </nav>
    <main class="max-w-5xl mx-auto px-4 py-8">@yield('content')</main>
</body>
</html>
