{{--
    Foto de portada de la colección «tendencias»: la del cliente en el tamaño justo o, si todavía no
    hay, un campo de color con las iniciales (nunca un hueco). Parámetros opcionales: widths (anchos
    del srcset), width (el de src), sizes, class. Necesita $page.
--}}
@if($page->heroImage)
    @php($photoSrcset = \App\Support\CloudinaryImage::srcset($page->heroImage, $widths ?? [480, 768, 1200]))
    <img
        class="tr-cover-img {{ $class ?? '' }}"
        src="{{ \App\Support\CloudinaryImage::url($page->heroImage, $width ?? 1200) }}"
        @if($photoSrcset) srcset="{{ $photoSrcset }}" sizes="{{ $sizes ?? '100vw' }}" @endif
        alt="{{ $page->welcome['imagen_hero_alt'] ?? '' }}"
        loading="eager"
        fetchpriority="high"
        decoding="async"
        @if(!empty($parallax)) data-parallax="{{ $parallax }}" @endif
    >
@else
    <span class="tr-photo-empty {{ $class ?? '' }}" aria-hidden="true">{{ $page->initials() }}</span>
@endif
