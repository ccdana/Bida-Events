{{-- Foto de una página del libro. Parámetros: photo (URL o {url, alt}), width (px de la versión), class, alt (por defecto). --}}
@php
    $photoUrl = is_array($photo) ? ($photo['url'] ?? '') : (string) $photo;
    $photoAlt = (is_array($photo) ? ($photo['alt'] ?? null) : null) ?: ($alt ?? '');
@endphp
@if($photoUrl !== '')
    <img class="{{ $class ?? '' }}" src="{{ \App\Support\CloudinaryImage::url($photoUrl, $width ?? 600) }}"
        alt="{{ $photoAlt }}" loading="lazy" decoding="async" draggable="false">
@endif
