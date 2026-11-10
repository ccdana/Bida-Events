<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>@yield('title', config('bida.brand'))</title>
    <meta name="description" content="@yield('description', __('Invitaciones digitales para bodas, bautizos, cumpleaños y todos tus eventos en Bolivia.'))">
    @include('layouts.partials.share-meta', ['share' => $share ?? \App\Support\ShareMeta::make(
        config('bida.brand').' | '.__('Invitaciones digitales'),
        __('Invitaciones digitales para bodas, bautizos, cumpleaños y todos tus eventos en Bolivia.'),
        \App\Support\ShareMeta::defaultImage(),
        url()->current(),
    )])
    <meta name="theme-color" content="#f4f4f2" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#131414" media="(prefers-color-scheme: dark)">
    @hasSection('robots')
        <meta name="robots" content="@yield('robots')">
    @elseif($canonical = \App\Support\Localization::canonicalUrl())
        <link rel="canonical" href="{{ $canonical }}">
    @endif
    @include('layouts.partials.hreflang')
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    @include('layouts.partials.theme-script')
    <script>
        document.documentElement.classList.add('js');
        // Equipo de pocos recursos o con ahorro de datos: los esqueletos quedan quietos, sin brillo («site-lite», skeleton.css).
        // «?hero=lite» y «?hero=live» lo fuerzan para probar; site.js lo suma también si mide que la pantalla va a menos de 20 fps.
        (function () {
            var forced = /[?&]hero=(lite|live)(&|$)/.exec(location.search);
            var conn = navigator.connection;
            var slow = (conn && conn.saveData === true)
                || (typeof navigator.deviceMemory === 'number' && navigator.deviceMemory <= 2)
                || (typeof navigator.hardwareConcurrency === 'number' && navigator.hardwareConcurrency <= 2);

            if (forced ? forced[1] === 'lite' : slow) {
                document.documentElement.classList.add('site-lite');
            }
        })();
        // Si site.js no llega a cargar, se muestra todo el contenido igual
        setTimeout(function () {
            if (!document.documentElement.classList.contains('site-motion')) {
                document.documentElement.classList.add('site-reveal-all');
            }
        }, 3500);
    </script>
    {{-- Serif editorial de los titulares y Outfit, las dos empaquetadas con Vite. Se precarga la serif (normal y cursiva
         de la palabra que rota) para que el titular salga con su letra desde el primer pintado, sin saltar. --}}
    <link rel="preload" as="font" type="font/woff2" crossorigin href="{{ Vite::asset('node_modules/@fontsource/instrument-serif/files/instrument-serif-latin-400-normal.woff2') }}">
    <link rel="preload" as="font" type="font/woff2" crossorigin href="{{ Vite::asset('node_modules/@fontsource/instrument-serif/files/instrument-serif-latin-400-italic.woff2') }}">
    {{-- Solo el CSS del sitio público (public.css), no el de las invitaciones ni el del panel --}}
    @vite(['resources/css/public.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="site min-h-[100dvh]">
    <a href="#contenido" class="site-skip">{{ __('Saltar al contenido') }}</a>

    @yield('content')

    @include('layouts.partials.cookie-notice')
</body>
</html>
