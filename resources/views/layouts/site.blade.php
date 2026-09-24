<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>@yield('title', config('bida.brand'))</title>
    <meta name="description" content="@yield('description', 'Invitaciones digitales para bodas, bautizos, cumpleaños y todos tus eventos en Bolivia.')">
    @include('layouts.partials.share-meta', ['share' => $share ?? \App\Support\ShareMeta::make(
        config('bida.brand').' | Invitaciones digitales',
        'Invitaciones digitales para bodas, bautizos, cumpleaños y todos tus eventos en Bolivia.',
        \App\Support\ShareMeta::defaultImage(),
        url()->current(),
    )])
    <meta name="theme-color" content="#f4f4f2" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#131414" media="(prefers-color-scheme: dark)">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    @include('layouts.partials.theme-script')
    <script>
        document.documentElement.classList.add('js');
        // Si site.js no llega a cargar, se muestra todo el contenido igual
        setTimeout(function () {
            if (!document.documentElement.classList.contains('site-motion')) {
                document.documentElement.classList.add('site-reveal-all');
            }
        }, 3500);
    </script>
    {{-- Serif editorial de los titulares; el resto usa Outfit (empaquetada con Vite) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="site min-h-[100dvh]">
    @yield('content')

    @include('layouts.partials.cookie-notice')
</body>
</html>
