{{--
    Vista previa al compartir el enlace (WhatsApp, Facebook, Telegram, X).
    Recibe $share de App\Support\ShareMeta: title, description, image, url, type y site.
--}}
<meta property="og:type" content="{{ $share['type'] }}">
<meta property="og:site_name" content="{{ $share['site'] }}">
<meta property="og:locale" content="es_BO">
<meta property="og:title" content="{{ $share['title'] }}">
<meta property="og:description" content="{{ $share['description'] }}">
<meta property="og:url" content="{{ $share['url'] }}">
<meta property="og:image" content="{{ $share['image'] }}">
@if(str_starts_with($share['image'], 'https://'))
    <meta property="og:image:secure_url" content="{{ $share['image'] }}">
@endif
<meta property="og:image:width" content="{{ \App\Support\ShareMeta::WIDTH }}">
<meta property="og:image:height" content="{{ \App\Support\ShareMeta::HEIGHT }}">
<meta property="og:image:alt" content="{{ $share['title'] }}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $share['title'] }}">
<meta name="twitter:description" content="{{ $share['description'] }}">
<meta name="twitter:image" content="{{ $share['image'] }}">
