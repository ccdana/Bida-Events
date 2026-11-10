{{--
    skel: la foto vive dentro de un esqueleto (.site-skel--fade, skeleton.css) y le avisa al llegar, o al fallar,
    para que el brillo se apague y ella se funda. Va en la propia etiqueta para funcionar antes de que cargue site.js.
--}}
@props(['key', 'alt' => null, 'priority' => false, 'skel' => false])

@php([$width, $height] = \App\Support\SiteImage::size($key))

<img src="{{ \App\Support\SiteImage::url($key) }}" alt="{{ $alt ?? \App\Support\SiteImage::alt($key) }}" width="{{ $width }}" height="{{ $height }}"
    @if($priority) fetchpriority="high" @else loading="lazy" @endif decoding="async"
    @if($skel) onload="this.parentNode.classList.add('is-live')" onerror="this.parentNode.classList.add('is-live')" @endif
    {{ $attributes }}>
