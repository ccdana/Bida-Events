<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    @include('layouts.partials.panel-head')
    <title>@yield('title', 'Editor') | {{ config('bida.brand') }}</title>
    {{-- Fuentes que se pueden elegir para la invitación (vista previa del panel Estética) --}}
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Cormorant+Garamond:wght@400;600;700&family=Cinzel:wght@400;600;700&family=Libre+Baskerville:wght@400;700&family=Bodoni+Moda:wght@400;600;700&family=Prata&family=Lora:wght@400;500;600;700&family=Merriweather:wght@300;400;700&family=Montserrat:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&family=Lato:wght@300;400;700&family=Nunito+Sans:wght@300;400;600;700&family=Source+Sans+3:wght@300;400;600;700&family=Poppins:wght@300;400;500;600;700&family=Raleway:wght@300;400;500;600;700&family=Open+Sans:wght@300;400;600;700&family=Great+Vibes&family=Parisienne&family=Alex+Brush&family=Dancing+Script:wght@400;700&family=Sacramento&family=Allura&family=Tangerine:wght@400;700&family=Petit+Formal+Script&family=Fredoka:wght@400;600&family=Creepster&family=Libre+Caslon+Display&family=Cormorant+Infant:wght@400;600&family=Barlow+Condensed:wght@400;600&family=Bagel+Fat+One&family=Special+Elite&family=Instrument+Sans:wght@400;600&family=Figtree:wght@400;600&family=Source+Serif+4:wght@400;600&family=Outfit:wght@400;600&family=Public+Sans:wght@400;600&display=swap" rel="stylesheet">
</head>
<body class="site admin-shell flex h-[100dvh] flex-col overflow-hidden">
    {{--
        Una franja delgada: en el celular la flecha, el nombre y Guardar (lo demás en «⋯»); en computadora el logo, el nombre
        y los accesos sueltos. Las vistas ponen sus enlaces en header-links, su botón principal en header-actions.
    --}}
    <header class="ed-top">
        <a href="{{ $editorHome ?? route('admin.dashboard') }}" class="ed-top__logo" aria-label="Volver al panel" title="Volver al panel">
            <x-brand.logo mark-class="h-7 w-auto" />
        </a>
        <a href="{{ $editorHome ?? route('admin.dashboard') }}" class="ed-top__back" aria-label="Volver al panel" title="Volver al panel">
            <x-phosphor-arrow-left-light aria-hidden="true" />
        </a>
        <p class="ed-top__title">@yield('header-title', 'Editor')</p>

        <div class="ed-top__actions">
            <div class="ed-top__links">@yield('header-links')</div>
            @yield('header-actions')
            <span class="ed-top__rule" aria-hidden="true"></span>
            <div class="ed-top__panel">@include('layouts.partials.panel-actions')</div>

            {{-- Celular: lo secundario se junta en «⋯» --}}
            <div class="ed-top__more" x-data="{ open: false }" @keydown.escape.window="open = false" @click.outside="open = false">
                <button type="button" class="admin-icon-button ed-top__dots" @click="open = !open" :aria-expanded="open.toString()" aria-label="Más opciones" title="Más opciones">
                    <x-phosphor-dots-three-light aria-hidden="true" />
                </button>
                <div class="ed-top__menu" x-show="open" x-cloak x-transition.opacity>
                    @yield('header-links')
                    <div class="ed-top__menu-foot">@include('layouts.partials.panel-actions')</div>
                </div>
            </div>
        </div>
    </header>

    {{-- Un aviso de lo que pasó (guardado, algo pendiente): flota sobre el editor, no lo empuja, y el de guardado se va solo --}}
    @if(session('success') || session('warning'))
        <div class="ed-flash-bar" x-data="{ open: true }" x-show="open" x-init="@if(! session('warning')) setTimeout(() => open = false, 6000) @endif" x-transition.opacity
            @click="open = false" role="status">
            @if(session('success'))
                <span class="ed-flash-bar__line">
                    <x-phosphor-check-circle-light class="ed-flash-bar__icon" aria-hidden="true" />
                    <span>{{ session('success') }}</span>
                </span>
            @endif
            @if(session('warning'))
                <span class="ed-flash-bar__line">
                    <x-phosphor-warning-circle-light class="ed-flash-bar__icon" aria-hidden="true" />
                    <span>{{ session('warning') }}</span>
                </span>
            @endif
        </div>
    @endif

    @if($errors->any())
        <div class="shrink-0 border-b border-site-line bg-site-surface px-4 py-3 text-sm" role="alert">
            <p class="flex items-center justify-center gap-2 font-medium text-site-danger">
                <x-phosphor-warning-circle class="size-5 shrink-0" aria-hidden="true" />
                No se guardaron los cambios. Revisa lo siguiente:
            </p>
            <ul class="mx-auto mt-1.5 max-h-24 max-w-3xl list-inside list-disc overflow-y-auto text-site-muted">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="min-h-0 flex-1">
        @yield('content')
    </div>
</body>
</html>
