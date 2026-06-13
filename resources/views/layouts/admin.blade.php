<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel') â€” Bida Events</title>
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
</head>
<body class="min-h-screen admin-shell antialiased">
    <nav class="sticky top-0 z-50 border-b border-stone-200/80 bg-white/90 backdrop-blur">
        <div class="mx-auto max-w-6xl px-4 py-3.5 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.dashboard') }}" class="font-serif text-xl tracking-wide text-stone-900">Bida <span class="text-amber-700">Events</span></a>
                <span class="hidden text-stone-300 sm:inline">|</span>
                <div class="min-w-0">
                    <p class="text-[10px] uppercase tracking-widest text-stone-400">Panel administrativo</p>
                    <p class="truncate text-sm text-stone-600">@yield('title', 'Panel')</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3 text-sm">
                <button type="button" onclick="toggleAdminTheme()" class="admin-link-button">Tema</button>
                <a href="{{ route('admin.invitations.create') }}" class="admin-primary-button">+ Nueva invitación</a>
                <span class="text-stone-400 hidden sm:inline">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-xs uppercase tracking-wider text-stone-500 hover:text-stone-800">Salir</button>
                </form>
            </div>
        </div>
    </nav>

    @if(session('success'))
        <div class="max-w-6xl mx-auto px-4 pt-4">
            <div class="rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">{{ session('success') }}</div>
        </div>
    @endif

    <main class="max-w-6xl mx-auto px-4 py-8">
        @yield('content')
    </main>
</body>
</html>
