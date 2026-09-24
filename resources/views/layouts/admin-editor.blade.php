<!DOCTYPE html>
<html lang="es">
<head>
    @include('layouts.partials.panel-head')
    <title>@yield('title', 'Editor') | {{ config('bida.brand') }}</title>
    {{-- Fuentes que se pueden elegir para la invitación (vista previa del panel Estética) --}}
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Cormorant+Garamond:wght@400;600;700&family=Cinzel:wght@400;600;700&family=Libre+Baskerville:wght@400;700&family=Bodoni+Moda:wght@400;600;700&family=Prata&family=Lora:wght@400;500;600;700&family=Merriweather:wght@300;400;700&family=Montserrat:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&family=Lato:wght@300;400;700&family=Nunito+Sans:wght@300;400;600;700&family=Source+Sans+3:wght@300;400;600;700&family=Poppins:wght@300;400;500;600;700&family=Raleway:wght@300;400;500;600;700&family=Open+Sans:wght@300;400;600;700&family=Great+Vibes&family=Parisienne&family=Alex+Brush&family=Dancing+Script:wght@400;700&family=Sacramento&family=Allura&family=Tangerine:wght@400;700&family=Petit+Formal+Script&display=swap" rel="stylesheet">
</head>
<body class="site admin-shell flex h-[100dvh] flex-col overflow-hidden">
    <header class="relative z-40 flex shrink-0 flex-col gap-3 border-b border-site-line bg-site-bg px-4 py-3 sm:h-16 sm:flex-row sm:items-center sm:justify-between sm:gap-4 sm:py-0 lg:px-6">
        <div class="flex min-w-0 items-center gap-4">
            <a href="{{ $editorHome ?? route('admin.dashboard') }}" class="shrink-0 text-lg">
                <x-brand.logo mark-class="h-8 w-auto" />
            </a>
            <span class="hidden h-7 w-px bg-site-line sm:block" aria-hidden="true"></span>
            <div class="min-w-0">
                <p class="text-xs text-site-muted">Editor de invitaciones</p>
                <p class="truncate text-sm font-medium">@yield('header-title', 'Editor')</p>
            </div>
        </div>
        <div class="flex flex-wrap items-center justify-end gap-2">
            @yield('header-actions')
            <span class="mx-1 hidden h-7 w-px bg-site-line sm:block" aria-hidden="true"></span>
            @include('layouts.partials.panel-actions')
        </div>
    </header>

    @if(session('success'))
        <p class="flex shrink-0 items-center justify-center gap-2 border-b border-site-line bg-site-tint px-4 py-2.5 text-sm" role="status">
            <x-phosphor-check-circle class="size-5 shrink-0 text-site-accent" aria-hidden="true" />
            {{ session('success') }}
        </p>
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
