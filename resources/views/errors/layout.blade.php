{{--
    Página de error con la marca, la misma para 403, 404, 419, 429, 500 y 503. Va sola, sin el diseño del
    sitio, sin sesión y sin base de datos: un error puede ocurrir justo cuando eso falla. Recibe $code.

    Los textos cambian según dónde ocurrió: una invitación o un enlace de la puerta que no abre lleva un
    mensaje para quien la recibió, no para quien navega el sitio. Sirve igual si la invitación venció o si
    el enlace estaba mal escrito: no se dice cuál de los dos es.
--}}
@php
    $english = request()->is('en', 'en/*');
    app()->setLocale($english ? 'en' : 'es');

    $inInvitation = request()->is('p/*', 'muestra/*');
    $atDoor = request()->is('puerta/*', 'entrada/*');
    $home = $english ? url('/en') : url('/');

    [$title, $message] = match (true) {
        $code === 404 && $inInvitation => [__('Esta invitación no está disponible'), __('Revisa que el enlace esté completo o pídele uno nuevo a quien te invitó. Si el evento ya pasó, puede que la invitación haya terminado.')],
        $code === 404 && $atDoor => [__('Este enlace de la puerta no sirve'), __('Pídele uno nuevo a quien organiza el evento.')],
        $code === 404 => [__('No encontramos esa página'), __('El enlace puede estar mal escrito o la página ya no existe. Vuelve al inicio y la encuentras desde ahí.')],
        $code === 403 => [__('No tienes acceso a esta página'), __('Si crees que es un error, vuelve al inicio e inténtalo desde ahí.')],
        $code === 419 => [__('La página caducó'), __('Por seguridad, esta página estuvo abierta demasiado tiempo. Vuelve atrás, actualízala e inténtalo otra vez.')],
        $code === 429 => [__('Demasiados intentos'), __('Espera un minuto y vuelve a intentarlo.')],
        $code === 503 => [__('Estamos en mantenimiento'), __('Volvemos en unos minutos. Gracias por la paciencia.')],
        default => [__('Algo salió mal de nuestro lado'), __('Ya quedó registrado. Intenta de nuevo en unos minutos.')],
    };

    // «Volver» solo si la persona venía de otra página de este mismo sitio
    $referer = request()->headers->get('referer');
    $back = $referer
        && parse_url($referer, PHP_URL_HOST) === request()->getHost()
        && rtrim((string) parse_url($referer, PHP_URL_PATH), '/') !== rtrim(request()->getPathInfo(), '/')
        ? $referer
        : null;
    $homeLabel = $inInvitation || $atDoor ? __('Conocer Bida Events') : __('Ir al inicio');
@endphp
<!DOCTYPE html>
<html lang="{{ $english ? 'en' : 'es' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#f4f4f2" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#131414" media="(prefers-color-scheme: dark)">
    <title>{{ $title }} | Bida Events</title>
    <link rel="icon" href="{{ url('/favicon.svg') }}" type="image/svg+xml">
    <style>
        :root { --ink: #1d1e20; --paper: #f4f4f2; --muted: #5d5f63; --line: #d9d9d4; --gold: #9a7420; }
        @media (prefers-color-scheme: dark) {
            :root { --ink: #f1f0ec; --paper: #131414; --muted: #a2a49f; --line: #34363a; --gold: #d4ab52; }
        }
        * { box-sizing: border-box; }
        html { color-scheme: light dark; }
        body {
            margin: 0;
            min-height: 100dvh;
            display: grid;
            grid-template-rows: auto 1fr auto;
            background: var(--paper);
            color: var(--ink);
            font: 400 1rem/1.6 ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
        }
        header, footer { padding: 1.25rem 1.5rem; }
        .brand { color: var(--ink); text-decoration: none; font-size: 1.05rem; letter-spacing: -0.01em; }
        .brand b { font-weight: 700; }
        .brand span { color: var(--muted); }
        main { display: grid; place-content: center; padding: 2rem 1.5rem 4rem; }
        .box { max-width: 34rem; }
        .code { margin: 0 0 1rem; color: var(--gold); font-size: 0.8rem; font-weight: 600; letter-spacing: 0.18em; }
        h1 {
            margin: 0;
            font: 400 clamp(2rem, 7vw, 3.1rem)/1.1 ui-serif, Georgia, 'Times New Roman', serif;
            letter-spacing: -0.015em;
            text-wrap: balance;
        }
        p.message { margin: 1.1rem 0 0; color: var(--muted); font-size: 1.05rem; text-wrap: pretty; }
        .actions { display: flex; flex-wrap: wrap; gap: 0.75rem; margin-top: 2rem; }
        .btn {
            display: inline-flex; align-items: center; justify-content: center;
            min-height: 3rem; padding: 0.65rem 1.5rem;
            border: 1px solid var(--ink); border-radius: 999px;
            background: var(--ink); color: var(--paper);
            font: inherit; font-weight: 600; font-size: 0.95rem; text-decoration: none;
        }
        .btn--ghost { background: transparent; color: var(--ink); border-color: var(--line); }
        .btn:focus-visible { outline: 2px solid var(--gold); outline-offset: 3px; }
        footer { color: var(--muted); font-size: 0.85rem; }
    </style>
</head>
<body>
    <header>
        <a class="brand" href="{{ $home }}"><b>Bida</b> <span>Events</span></a>
    </header>

    <main>
        <div class="box">
            <p class="code">{{ $code }}</p>
            <h1>{{ $title }}</h1>
            <p class="message">{{ $message }}</p>

            <div class="actions">
                <a class="btn" href="{{ $home }}">{{ $homeLabel }}</a>
                @if($back && ! $inInvitation && ! $atDoor)
                    <a class="btn btn--ghost" href="{{ $back }}">{{ __('Volver') }}</a>
                @endif
            </div>
        </div>
    </main>

    <footer>© {{ date('Y') }} Bida Events</footer>
</body>
</html>
