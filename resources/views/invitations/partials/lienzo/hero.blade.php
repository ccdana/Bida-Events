{{--
    Portada de «Lienzo»: solo tipografía y espacio. Arriba la frase, el nombre en grande, un filete,
    el mensaje y la fecha con la hora. La foto, si la hay, va debajo en un recuadro limpio (sin velo
    ni recortes decorativos), para que la invitación se vea igual de bien con o sin ella.
--}}
@php
    $heroEyebrow = ($page->welcome['subtitulo'] ?? null) ?: ($invCopy['hero_eyebrow'] ?? 'Estás invitado');
    $heroMessage = $page->welcome['mensaje'] ?? '';
    $heroDay = ($page->welcome['fecha_texto'] ?? null)
        ?: \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('l j \d\e F \d\e Y'));
@endphp

<header id="inicio" class="inv-hero inv-lienzo-hero">
    <div class="inv-lienzo-hero__inner">
        <p class="inv-lienzo-hero__eyebrow inv-fade-up">{{ $heroEyebrow }}</p>
        <h1 class="inv-lienzo-hero__name inv-fade-up inv-fade-up--1">{{ $page->displayName }}</h1>
        <span class="inv-lienzo-hero__rule inv-fade-up inv-fade-up--2" aria-hidden="true"></span>

        @if(!empty($heroMessage))
            <p class="inv-lienzo-hero__message inv-fade-up inv-fade-up--2">{{ $heroMessage }}</p>
        @endif

        <p class="inv-lienzo-hero__date inv-fade-up inv-fade-up--3">
            <span>{{ $heroDay }}</span>
            <span class="inv-lienzo-hero__time">{{ $page->eventDate->format('H:i') }}</span>
        </p>

        @if($page->heroImage)
            @php($heroSrcset = \App\Support\CloudinaryImage::srcset($page->heroImage, [480, 768, 1200]))
            <figure class="inv-lienzo-hero__photo inv-fade-up inv-fade-up--3">
                <img
                    src="{{ \App\Support\CloudinaryImage::url($page->heroImage, 1200) }}"
                    @if($heroSrcset) srcset="{{ $heroSrcset }}" sizes="(min-width: 640px) 26rem, 90vw" @endif
                    alt=""
                    loading="eager"
                    fetchpriority="high"
                    decoding="async"
                >
            </figure>
        @endif
    </div>

    <a href="#contenido" class="inv-hero__scroll inv-lienzo-hero__scroll">
        Desliza
        <span class="inv-hero__scroll-line" aria-hidden="true"></span>
    </a>
</header>
