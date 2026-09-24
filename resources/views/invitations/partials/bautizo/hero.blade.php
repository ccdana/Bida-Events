{{-- Portada del bautizo: luz suave desde arriba, paloma que desciende sobre la foto en óvalo, nombre y fecha sobre nubes --}}
@php
    $heroEyebrow = ($page->welcome['subtitulo'] ?? null) ?: ($invCopy['hero_eyebrow'] ?? 'Mi bautizo');
    $heroMessage = $page->welcome['mensaje'] ?? '';
    $heroDateText = ($page->welcome['fecha_texto'] ?? null)
        ?: \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('l j \d\e F'));
@endphp

<header id="inicio" class="inv-hero inv-bautizo-hero">
    <div class="inv-bautizo-hero__light" aria-hidden="true">
        <span></span><span></span><span></span>
    </div>

    <div class="inv-bautizo-hero__inner">
        <p class="inv-bautizo-hero__eyebrow inv-fade-up">{{ $heroEyebrow }}</p>

        <div class="inv-bautizo-medallion inv-fade-up inv-fade-up--1">
            <div class="inv-bautizo-medallion__window">
                @if($page->heroImage)
                    @php($heroSrcset = \App\Support\CloudinaryImage::srcset($page->heroImage, [480, 768, 1200]))
                    <img
                        src="{{ \App\Support\CloudinaryImage::url($page->heroImage, 1200) }}"
                        @if($heroSrcset) srcset="{{ $heroSrcset }}" sizes="(min-width: 640px) 17rem, 64vw" @endif
                        alt=""
                        class="inv-bautizo-medallion__photo"
                        loading="eager"
                        fetchpriority="high"
                        decoding="async"
                    >
                @else
                    <span class="inv-bautizo-medallion__initial">{{ $page->initials() }}</span>
                @endif
            </div>
            @include('invitations.partials.bautizo.dove', ['class' => 'inv-bautizo-medallion__dove'])
        </div>

        <h1 class="inv-bautizo-hero__name inv-fade-up inv-fade-up--2">{{ $page->displayName }}</h1>

        @if(!empty($heroMessage))
            <p class="inv-bautizo-hero__message inv-fade-up inv-fade-up--2">{{ $heroMessage }}</p>
        @endif

        <p class="inv-bautizo-hero__date inv-fade-up inv-fade-up--3">
            <span>{{ $heroDateText }}</span>
            <i class="inv-bautizo-star" aria-hidden="true"></i>
            <span>{{ $page->eventDate->format('H:i') }}</span>
        </p>

        <a href="#contenido" class="inv-hero__scroll inv-bautizo-hero__scroll">
            {{ $invCopy['scroll_hint'] ?? 'Desliza' }}
            <span class="inv-hero__scroll-line" aria-hidden="true"></span>
        </a>
    </div>

    @include('invitations.partials.bautizo.clouds-band')
</header>
