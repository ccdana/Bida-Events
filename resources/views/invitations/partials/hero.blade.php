@php
    $heroTitle = $bienvenida['nombre_quinceanera'] ?? $invitation->title;
    $heroEyebrow = $bienvenida['subtitulo'] ?? 'Mis XV Años';
    $heroMessage = $bienvenida['mensaje'] ?? '';
    $heroDate = $bienvenida['fecha_texto'] ?? $invitation->event_date->locale('es')->translatedFormat('j \d\e F, Y');
@endphp

<header id="inicio" class="inv-hero {{ $hasHeroImage ? 'inv-hero--image' : 'inv-hero--plain' }}">
    @if($hasHeroImage)
        @php($heroSrcset = \App\Support\CloudinaryImage::srcset($heroImage, [768, 1280, 1920]))
        <div class="inv-hero__media" aria-hidden="true">
            <img
                src="{{ \App\Support\CloudinaryImage::url($heroImage, 1920) }}"
                @if($heroSrcset) srcset="{{ $heroSrcset }}" sizes="100vw" @endif
                alt=""
                class="inv-hero__image"
                loading="eager"
                fetchpriority="high"
                decoding="async"
            >
        </div>
        <div class="inv-hero__veil" aria-hidden="true"></div>
    @endif

    <div class="inv-hero__content">
        <p class="inv-hero__eyebrow inv-fade-up">{{ $heroEyebrow }}</p>

        <h1 class="inv-hero__title inv-fade-up inv-fade-up--1">{{ $heroTitle }}</h1>

        <div class="inv-hero__rule inv-fade-up inv-fade-up--2" aria-hidden="true"></div>

        @if(!empty($heroMessage))
            <p class="inv-hero__message inv-fade-up inv-fade-up--2">{{ $heroMessage }}</p>
        @endif

        <p class="inv-hero__date inv-fade-up inv-fade-up--3">{{ $heroDate }}</p>
    </div>

    <a href="#contenido" class="inv-hero__scroll">
        Desliza
        <span class="inv-hero__scroll-line" aria-hidden="true"></span>
    </a>
</header>
