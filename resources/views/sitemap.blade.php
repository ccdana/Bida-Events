{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
{{-- Solo páginas públicas: las invitaciones, las muestras y los paneles quedan fuera (ver public/robots.txt) --}}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach($urls as $url)
    <url>
        <loc>{{ $url }}</loc>
        <changefreq>monthly</changefreq>
    </url>
@endforeach
</urlset>
