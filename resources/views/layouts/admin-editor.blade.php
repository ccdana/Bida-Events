<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Editor') — Bida Events</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="h-screen overflow-hidden bg-stone-100 text-stone-800 antialiased flex flex-col">
    <header class="shrink-0 h-14 border-b border-stone-200 bg-white flex items-center justify-between px-4 z-50">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.dashboard') }}" class="font-serif text-lg text-stone-900">Bida <span class="text-amber-700">Events</span></a>
            <span class="text-stone-300">|</span>
            <span class="text-sm text-stone-500">@yield('header-title', 'Editor')</span>
        </div>
        <div class="flex items-center gap-3">
            @yield('header-actions')
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-xs text-stone-500 hover:text-stone-800 uppercase tracking-wider">Salir</button>
            </form>
        </div>
    </header>

    @if(session('success'))
        <div class="shrink-0 bg-emerald-50 border-b border-emerald-200 text-emerald-800 px-4 py-2 text-sm text-center">{{ session('success') }}</div>
    @endif

    <div class="flex-1 min-h-0">
        @yield('content')
    </div>
</body>
</html>
