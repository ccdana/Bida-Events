{{--
    Portada de la tarjeta «Sobre lacrado»: la foto de los dos a pantalla completa, velada en vino, y
    encima la frase del día en letra manuscrita con un corazón dibujado a mano. Debajo, para quién es
    la carta y quién la manda. Si no hay foto queda el degradado y el texto solos.
    En modo historia cada parte entra en su turno (data-step) y la foto se mueve con la inclinación.
--}}
@php
    $heroPhrase = ($page->welcome['subtitulo'] ?? null) ?: ($invCopy['hero_eyebrow'] ?? 'Feliz Día del Amor y la Amistad');
    $heroImageAlt = ($page->welcome['imagen_hero_alt'] ?? null) ?: ($cardTo !== '' ? 'Foto para '.$cardTo : '');
    $heroDate = \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('j \d\e F'));
@endphp

<header id="inicio" class="inv-sobre-hero">
    @if($page->heroImage)
        @php($heroSrcset = \App\Support\CloudinaryImage::srcset($page->heroImage, [480, 768, 1200]))
        <div class="inv-sobre-hero__photo" aria-hidden="{{ $heroImageAlt === '' ? 'true' : 'false' }}">
            <img src="{{ \App\Support\CloudinaryImage::url($page->heroImage, 1200) }}"
                @if($heroSrcset) srcset="{{ $heroSrcset }}" sizes="100vw" @endif
                alt="{{ $heroImageAlt }}" loading="eager" fetchpriority="high" decoding="async" draggable="false">
        </div>
    @endif

    <div class="inv-sobre-hero__inner">
        <p class="inv-sobre-hero__eyebrow" data-step style="--step: 0">{{ $heroDate }}</p>

        <h1 class="inv-sobre-hero__title" data-step style="--step: 1">
            <span class="inv-sobre-hero__script">{{ $heroPhrase }}</span>
            <svg class="inv-sobre-hero__flourish" viewBox="0 0 220 44" fill="none" aria-hidden="true">
                <path d="M6 30 C 40 6, 62 6, 74 20 C 82 30, 74 40, 64 36 C 52 31, 60 16, 84 16 C 118 16, 140 34, 176 22 C 194 16, 206 22, 214 30"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" />
            </svg>
        </h1>

        <p class="inv-sobre-hero__to" data-step style="--step: 3">
            {{ $cardTo !== '' ? 'Para '.$cardTo : $page->displayName }}
        </p>

        @if($cardFrom !== '')
            <p class="inv-sobre-hero__from" data-step style="--step: 4">de {{ $cardFrom }}</p>
        @endif

        <a href="#contenido" class="inv-sobre-hero__scroll" data-step style="--step: 5">
            Seguir leyendo
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M12 5v14M6 13l6 6 6-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
    </div>
</header>
