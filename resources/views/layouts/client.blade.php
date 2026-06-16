<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Mi Evento') — Bida Events</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media (prefers-color-scheme: dark) {
            body {
                @apply bg-slate-950 text-slate-100;
            }
            .client-header {
                @apply border-slate-800 bg-slate-900;
            }
            .client-card {
                @apply border-slate-800 bg-slate-800;
            }
            .client-card h2 {
                @apply text-slate-100;
            }
            .client-card p {
                @apply text-slate-400;
            }
            .client-badge-confirmed {
                @apply bg-emerald-950 text-emerald-300;
            }
            .client-badge-pending {
                @apply bg-slate-700 text-slate-300;
            }
            .client-btn-primary {
                @apply bg-amber-600 text-white hover:bg-amber-700;
            }
            .client-btn-secondary {
                @apply border-slate-600 text-slate-300 hover:bg-slate-800;
            }
            .client-main-title {
                @apply text-slate-100;
            }
            .client-subtitle {
                @apply text-slate-400;
            }
        }
        
        @media (prefers-color-scheme: light) {
            body {
                @apply bg-stone-50 text-stone-800;
            }
            .client-header {
                @apply border-stone-200 bg-white;
            }
            .client-card {
                @apply border-stone-200 bg-white;
            }
            .client-card h2 {
                @apply text-stone-900;
            }
            .client-card p {
                @apply text-stone-500;
            }
            .client-badge-confirmed {
                @apply bg-emerald-50 text-emerald-800;
            }
            .client-badge-pending {
                @apply bg-stone-100 text-stone-600;
            }
            .client-btn-primary {
                @apply bg-stone-900 text-white hover:bg-stone-800;
            }
            .client-btn-secondary {
                @apply border-stone-300 text-stone-700 hover:bg-stone-50;
            }
            .client-main-title {
                @apply text-stone-900;
            }
            .client-subtitle {
                @apply text-stone-500;
            }
        }
    </style>
</head>
<body class="min-h-screen antialiased transition-colors">
    <nav class="border-b client-header sticky top-0 z-50 shadow-sm">
        <div class="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-amber-600 to-amber-700 flex items-center justify-center text-white font-serif text-lg shadow-md">
                    BE
                </div>
                <div>
                    <a href="{{ route('client.dashboard') }}" class="font-serif text-lg font-semibold block">
                        <span class="text-amber-600">Bida</span>Events
                    </a>
                    <p class="text-xs opacity-60">Panel de cliente</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ auth()->user()?->is_admin ? route('admin.dashboard') : '#' }}" 
                   @if(!auth()->user()?->is_admin) style="display: none;" @endif
                   class="text-sm font-medium text-amber-600 hover:text-amber-700 transition">
                    Panel Admin
                </a>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-sm font-medium opacity-70 hover:opacity-100 transition">
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </div>
    </nav>
    <main class="max-w-5xl mx-auto px-4 py-8">
        @yield('content')
    </main>
</body>
</html>
