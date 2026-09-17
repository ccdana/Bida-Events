{{--
    Portada de la carta de amor: la foto de los dos como una instantánea sujeta con cinta de papel,
    la frase de la temporada y «Para …» en letra manuscrita. Si no hay foto, queda el texto solo.
    En modo historia cada parte entra en su turno (data-step) y la foto se inclina con el teléfono.
--}}
@php
    $heroPhrase = ($page->welcome['subtitulo'] ?? null) ?: ($invCopy['hero_eyebrow'] ?? 'Feliz Día del Amor');
    $heroMessage = trim((string) ($page->welcome['mensaje'] ?? ''));
    $heroImageAlt = ($page->welcome['imagen_hero_alt'] ?? null) ?: ($cardTo !== '' ? 'Foto para '.$cardTo : '');
@endphp

<header id="inicio" class="inv-amor-hero">
    <div class="inv-amor-hero__inner">
        <p class="inv-amor-hero__phrase inv-fade-up" data-step style="--step: 0">{{ $heroPhrase }}</p>

        @if($page->heroImage)
            @php($heroSrcset = \App\Support\CloudinaryImage::srcset($page->heroImage, [480, 768, 1200]))
            <figure class="inv-amor-hero__photo inv-fade-up inv-fade-up--1" data-step style="--step: 1">
                <span class="inv-amor-hero__tape" aria-hidden="true"></span>
                <img src="{{ \App\Support\CloudinaryImage::url($page->heroImage, 900) }}"
                    @if($heroSrcset) srcset="{{ $heroSrcset }}" sizes="(min-width: 768px) 24rem, 78vw" @endif
                    alt="{{ $heroImageAlt }}" loading="eager" fetchpriority="high" decoding="async" draggable="false">
                <span class="inv-amor-hero__glare" aria-hidden="true"></span>
            </figure>
        @endif

        <h1 class="inv-amor-hero__to inv-fade-up inv-fade-up--2" data-step style="--step: 3">
            {{ $cardTo !== '' ? 'Para '.$cardTo : $page->displayName }}
        </h1>

        @if($heroMessage !== '')
            <p class="inv-amor-hero__message inv-fade-up inv-fade-up--3" data-step style="--step: 5">{{ $heroMessage }}</p>
        @endif

        @if($cardFrom !== '')
            <p class="inv-amor-hero__from inv-fade-up inv-fade-up--3" data-step style="--step: 6">De {{ $cardFrom }}</p>
        @endif

        <a href="#{{ $page->visible('dedicatoria') ? 'dedicatoria' : 'contenido' }}" class="inv-amor-hero__scroll inv-fade-up inv-fade-up--3" data-step style="--step: 7">
            Leer la carta
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M12 5v14M6 13l6 6 6-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
    </div>
</header>
