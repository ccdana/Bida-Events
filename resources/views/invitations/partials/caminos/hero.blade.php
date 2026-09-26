{{--
    Portada de «Dos caminos»: cada nombre en un extremo del mapa y un camino punteado que sale de
    cada uno (el primero en el color principal, el segundo en el secundario) hasta el aro donde se
    juntan: la foto, o las iniciales si no hay. Debajo, la fecha, la hora y el lugar.
    Los caminos se dibujan al abrirse la portada (themes/caminos.css).
--}}
@php
    $names = $page->names();
    $first = $names[0] ?? $page->displayName;
    $second = $names[1] ?? null;
    $heroEyebrow = ($page->welcome['subtitulo'] ?? null) ?: ($invCopy['hero_eyebrow'] ?? 'Nos casamos');
    $heroMessage = $page->welcome['mensaje'] ?? '';
    $heroDay = ($page->welcome['fecha_texto'] ?? null)
        ?: $page->eventDate->locale('es')->translatedFormat('j \d\e F \d\e Y');
@endphp

<header id="inicio" class="inv-hero dc-hero">
    <div class="dc-hero__map">
        <p class="dc-hero__eyebrow inv-fade-up">{{ $heroEyebrow }}</p>

        {{-- Los dos caminos: cada uno en su capa para revelarse desde su nombre --}}
        <svg class="dc-path dc-path--a" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true" focusable="false">
            <path d="M9 21 C14 33 26 27 32 35 S44 45 50 50"/>
        </svg>
        <svg class="dc-path dc-path--b" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true" focusable="false">
            <path d="M91 80 C86 70 74 76 68 66 S56 56 50 50"/>
        </svg>

        <h1 class="dc-names">
            <span class="dc-name dc-name--a inv-fade-up inv-fade-up--1">{{ $first }}</span>
            @if($second)
                <span class="dc-name__and">y</span>
                <span class="dc-name dc-name--b inv-fade-up inv-fade-up--2">{{ $second }}</span>
            @endif
        </h1>

        <figure class="dc-meet inv-fade-up inv-fade-up--2">
            @if($page->heroImage)
                @php($heroSrcset = \App\Support\CloudinaryImage::srcset($page->heroImage, [480, 768, 1200]))
                {{-- La foto es el centro del mapa: grande, nítida en pantallas de alta densidad y con parallax --}}
                <img
                    data-parallax="0.07"
                    src="{{ \App\Support\CloudinaryImage::url($page->heroImage, 1200) }}"
                    @if($heroSrcset) srcset="{{ $heroSrcset }}" sizes="(min-width: 640px) 25rem, 66vw" @endif
                    alt=""
                    loading="eager"
                    fetchpriority="high"
                    decoding="async"
                >
            @else
                <span class="dc-meet__initials">{{ $page->initials() }}</span>
            @endif
        </figure>
        <span class="dc-meet__orbit inv-fade-up inv-fade-up--2" aria-hidden="true"></span>
    </div>

    <div class="dc-hero__details inv-fade-up inv-fade-up--3">
        <p class="dc-hero__meet">{{ $invCopy['hero_meet'] ?? 'Nuestros caminos se juntan el' }}</p>
        <p class="dc-hero__date">{{ $heroDay }}</p>
        <p class="dc-hero__where">
            <span>{{ $page->eventDate->format('H:i') }}</span>
            @if($page->placeName)
                <span>{{ $page->placeName }}</span>
            @endif
        </p>
        @if(!empty($heroMessage))
            <p class="dc-hero__message">{{ $heroMessage }}</p>
        @endif
    </div>

    <a href="#contenido" class="inv-hero__scroll dc-hero__scroll">
        {{ $invCopy['scroll_hint'] ?? 'Desliza' }}
        <span class="inv-hero__scroll-line" aria-hidden="true"></span>
    </a>
</header>
