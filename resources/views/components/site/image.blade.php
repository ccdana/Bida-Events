@props(['key', 'alt' => null, 'priority' => false])

@php([$width, $height] = \App\Support\SiteImage::size($key))

<img src="{{ \App\Support\SiteImage::url($key) }}" alt="{{ $alt ?? \App\Support\SiteImage::alt($key) }}" width="{{ $width }}" height="{{ $height }}"
    @if($priority) fetchpriority="high" @else loading="lazy" @endif decoding="async"
    {{ $attributes }}>
