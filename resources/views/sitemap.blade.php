{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
{{-- Solo páginas públicas: las invitaciones, las muestras, los paneles y la puerta quedan fuera (ver SeoController::robots) --}}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
@foreach($urls as $url)
    <url>
        <loc>{{ $url['loc'] }}</loc>
@foreach($url['alternates'] as $code => $href)
        <xhtml:link rel="alternate" hreflang="{{ $code }}" href="{{ $href }}"/>
@endforeach
        <lastmod>{{ $url['lastmod'] }}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>{{ $url['priority'] }}</priority>
    </url>
@endforeach
</urlset>
