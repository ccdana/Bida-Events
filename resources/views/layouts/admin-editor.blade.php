<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Editor') — Bida Events</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Cormorant+Garamond:wght@400;600;700&family=Cinzel:wght@400;600;700&family=Libre+Baskerville:wght@400;700&family=Bodoni+Moda:wght@400;600;700&family=Prata&family=Lora:wght@400;500;600;700&family=Merriweather:wght@300;400;700&family=Montserrat:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&family=Lato:wght@300;400;700&family=Nunito+Sans:wght@300;400;600;700&family=Source+Sans+3:wght@300;400;600;700&family=Poppins:wght@300;400;500;600;700&family=Raleway:wght@300;400;500;600;700&family=Open+Sans:wght@300;400;600;700&family=Great+Vibes&family=Parisienne&family=Alex+Brush&family=Dancing+Script:wght@400;700&family=Sacramento&family=Allura&family=Tangerine:wght@400;700&family=Petit+Formal+Script&display=swap" rel="stylesheet">
    <script>
        (function () {
            const theme = localStorage.getItem('admin-theme') || 'light';
            document.documentElement.dataset.adminTheme = theme;
        })();
        function toggleAdminTheme() {
            const next = document.documentElement.dataset.adminTheme === 'dark' ? 'light' : 'dark';
            document.documentElement.dataset.adminTheme = next;
            localStorage.setItem('admin-theme', next);
        }
    </script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="h-screen overflow-hidden admin-shell antialiased flex flex-col">
    <header class="shrink-0 border-b border-stone-200 bg-white/95 backdrop-blur flex flex-col gap-3 px-4 py-3 z-50 sm:h-16 sm:flex-row sm:items-center sm:justify-between sm:gap-4 sm:py-0 relative overflow-hidden">
        <div class="absolute inset-x-0 bottom-0 h-px" style="background: linear-gradient(90deg, transparent, rgba(44,24,16,.42), rgba(201,169,110,.55), transparent);"></div>
        <div class="min-w-0 flex items-center gap-3">
            <a href="{{ route('admin.dashboard') }}" class="shrink-0 font-serif text-lg text-stone-900">Bida <span class="text-amber-700">Events</span></a>
            <span class="hidden text-stone-300 sm:inline">|</span>
            <div class="min-w-0">
                <p class="text-[10px] uppercase tracking-widest text-stone-400">Editor de invitaciones</p>
                <p class="truncate text-sm font-medium text-stone-700">@yield('header-title', 'Editor')</p>
            </div>
        </div>
        <div class="flex flex-wrap items-center justify-end gap-3">
            <button type="button" onclick="toggleAdminTheme()" class="admin-link-button">Tema</button>
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
