<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel') — Bida Events</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-50 text-stone-800 antialiased">
    <nav class="border-b border-stone-200 bg-white/80 backdrop-blur-sm sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ route('admin.dashboard') }}" class="font-serif text-xl tracking-wide text-stone-900">Bida <span class="text-amber-700">Events</span></a>
            <div class="flex items-center gap-4 text-sm">
                <span class="text-stone-500 hidden sm:inline">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-stone-600 hover:text-stone-900 transition">Salir</button>
                </form>
            </div>
        </div>
    </nav>

    @if(session('success'))
        <div class="max-w-6xl mx-auto px-4 pt-4">
            <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">{{ session('success') }}</div>
        </div>
    @endif

    <main class="max-w-6xl mx-auto px-4 py-8">
        @yield('content')
    </main>
</body>
</html>
