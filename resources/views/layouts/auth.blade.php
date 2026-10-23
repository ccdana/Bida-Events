{{--
    Páginas de acceso (ingresar, crear cuenta, recuperar la contraseña, confirmar el correo): el
    formulario a la izquierda y, en pantallas anchas, las fotos de los eventos que rotan a la derecha.
    Cada página llena la sección «auth». El rotador sincroniza las fotos con cualquier
    data-rotator-group del formulario (la frase del subtítulo del login).
--}}
@extends('layouts.site')

@section('content')
    @php($showcase = config('bida.showcase'))

    <main id="contenido" tabindex="-1" data-rotator data-rotator-interval="3600" class="grid min-h-[100dvh] lg:grid-cols-[minmax(0,1fr)_minmax(0,1.05fr)]">
        <div class="flex flex-col px-5 py-6 sm:px-10 lg:px-16">
            <div class="flex items-center justify-between gap-4">
                <a href="{{ lroute('home') }}" class="text-lg">
                    <x-brand.logo animated />
                </a>
                <div class="flex items-center gap-4">
                    <x-site.language-switch touch />
                    @include('layouts.partials.theme-toggle')
                </div>
            </div>

            <div class="site-auth mx-auto my-auto w-full max-w-sm py-14 lg:mx-0">
                @yield('auth')
            </div>

            <p class="text-sm text-site-muted">© {{ now()->year }} {{ config('bida.brand') }}</p>
        </div>

        <div class="hidden p-4 lg:flex" aria-hidden="true">
            <div class="site-stage size-full" data-rotator-group>
                @foreach($showcase as $index => $event)
                    <x-site.image :key="$event['image']" :priority="$index === 0" :class="$index === 0 ? 'is-active' : ''" />
                @endforeach
            </div>
        </div>
    </main>

    {{-- Tras un error, el foco va al primer campo con error (que lleva su mensaje en aria-describedby): así el teclado
         y los lectores de pantalla llegan directo a lo que hay que corregir --}}
    <script>
        (function () {
            var invalid = document.querySelector('.site-auth [aria-invalid="true"]');

            if (invalid) {
                invalid.focus();
            }
        })();
    </script>
@endsection
