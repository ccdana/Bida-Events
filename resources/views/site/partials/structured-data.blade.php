{{--
    Datos estructurados (schema.org, JSON-LD) para Google y los motores de respuesta (Google AI
    Overviews, ChatGPT, Perplexity, Gemini). Describe la marca como una sola entidad (@id estable en
    todas las páginas), el sitio, el servicio con sus precios reales y, si la página las tiene, sus
    preguntas frecuentes y su ruta de navegación. Todo sale de la configuración: nunca promete algo que
    la página no muestra.

    Recibe (todos opcionales): $faqs (lista de [pregunta, respuesta]), $offers (lista de
    ['name', 'price', 'description']), $serviceName, $breadcrumbs (lista de [nombre, url]) y $article
    (['headline', 'description', 'url', 'published', 'modified'], para las guías: firma la marca como autora y editora).
--}}
@php
    $brand = config('bida.brand');
    $organizationId = lroute('home').'#organizacion';
    $sameAs = array_values(array_filter([
        config('bida.instagram') ? 'https://www.instagram.com/'.config('bida.instagram').'/' : null,
        config('bida.facebook') ? 'https://www.facebook.com/'.config('bida.facebook') : null,
        config('bida.tiktok') ? 'https://www.tiktok.com/@'.config('bida.tiktok') : null,
    ]));
    $offers ??= collect(\App\Support\Offers::tierOffers())->map(fn (array $package) => [
        'name' => __('Hazlo tú :name (pago único)', ['name' => $package['name']]),
        'price' => $package['final_price'],
        'description' => $package['summary'] ?? '',
    ])->all();

    $graph = [
        [
            '@type' => 'Organization',
            '@id' => $organizationId,
            'name' => $brand,
            'url' => lroute('home'),
            'logo' => asset('favicon.svg'),
            'email' => config('bida.email'),
            'telephone' => '+'.ltrim((string) config('bida.whatsapp'), '+'),
            'address' => ['@type' => 'PostalAddress', 'addressLocality' => config('bida.city'), 'addressCountry' => 'BO'],
            'areaServed' => [__('Bolivia'), __('Latinoamérica')],
            'knowsAbout' => ['invitaciones digitales', __('invitaciones web para bodas'), __('invitaciones de XV años'), __('confirmación de asistencia (RSVP)'), __('control de entrada con código QR')],
            'sameAs' => $sameAs,
        ],
        [
            '@type' => 'WebSite',
            '@id' => lroute('home').'#sitio',
            'url' => lroute('home'),
            'name' => $brand,
            'inLanguage' => 'es',
            'publisher' => ['@id' => $organizationId],
        ],
        [
            '@type' => 'Service',
            'name' => $serviceName ?? __('Invitaciones digitales para eventos'),
            'serviceType' => __('Invitación digital (página web del evento)'),
            'provider' => ['@id' => $organizationId],
            'areaServed' => [__('Bolivia'), __('Latinoamérica')],
            'offers' => array_map(fn (array $offer) => [
                '@type' => 'Offer',
                'name' => $offer['name'],
                'price' => (string) $offer['price'],
                'priceCurrency' => \App\Support\Money::code(),
                'description' => $offer['description'] ?? '',
                'availability' => 'https://schema.org/InStock',
            ], $offers),
        ],
    ];

    if (! empty($faqs)) {
        $graph[] = [
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn (array $faq) => [
                '@type' => 'Question',
                'name' => $faq[0],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq[1]],
            ], $faqs),
        ];
    }

    if (! empty($article)) {
        $graph[] = [
            '@type' => 'Article',
            'headline' => $article['headline'],
            'description' => $article['description'],
            'url' => $article['url'],
            'mainEntityOfPage' => $article['url'],
            'inLanguage' => 'es',
            'datePublished' => $article['published'],
            'dateModified' => $article['modified'],
            'author' => ['@id' => $organizationId],
            'publisher' => ['@id' => $organizationId],
            'about' => [__('invitación digital'), __('confirmación de asistencia'), __('control de entrada con código QR')],
        ];
    }

    if (! empty($breadcrumbs)) {
        $graph[] = [
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_map(fn (array $crumb, int $position) => [
                '@type' => 'ListItem',
                'position' => $position + 1,
                'name' => $crumb[0],
                'item' => $crumb[1],
            ], $breadcrumbs, array_keys($breadcrumbs)),
        ];
    }
@endphp
<script type="application/ld+json">{!! json_encode(['@context' => 'https://schema.org', '@graph' => $graph], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
