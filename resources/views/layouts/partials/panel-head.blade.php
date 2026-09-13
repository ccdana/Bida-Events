{{-- Cabecera común de los paneles (admin, editor y cliente) --}}
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="theme-color" content="#f4f4f2" media="(prefers-color-scheme: light)">
<meta name="theme-color" content="#131414" media="(prefers-color-scheme: dark)">
<link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
@include('layouts.partials.theme-script')
@vite(['resources/css/app.css', 'resources/js/app.js'])
