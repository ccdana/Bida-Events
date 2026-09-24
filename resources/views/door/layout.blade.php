{{--
    Pantallas del personal de la puerta: sin menú ni pie, letra grande y un solo botón principal por
    pantalla. No se indexan. Estilos: resources/css/site/site.css («Puerta»).
--}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#131414">
    <title>@yield('title') · Puerta</title>
    @include('layouts.partials.theme-script')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="site door">
    <header class="door-bar">
        <span class="door-bar__label">Puerta</span>
        <span class="door-bar__event">{{ $invitation->title }}</span>
        <span class="door-bar__count" aria-label="Personas que ingresaron">
            <b>{{ $stats['arrived'] }}</b>/{{ $stats['expected'] }}
        </span>
    </header>

    <main class="door-main">
        @yield('content')
    </main>
</body>
</html>
