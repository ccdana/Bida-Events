{{--
    Versiones de la página en cada idioma para los buscadores (hreflang), con x-default en la raíz.
    Solo las páginas traducidas las tienen (App\Support\Localization::alternates).
--}}
@foreach(\App\Support\Localization::alternates() as $code => $url)
    <link rel="alternate" hreflang="{{ $code }}" href="{{ $url }}">
@endforeach
