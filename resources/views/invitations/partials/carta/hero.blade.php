{{--
    Portada de «Carta de baile»: la carta abierta sobre el terciopelo. Arriba el ojal por donde pasa
    el cordón; el nombre en grande, la foto sujeta con cuatro esquineros y, como en las cartas de los
    bailes, el renglón «Reservado para» con el nombre del invitado. La fecha, la hora y el salón van
    en renglones con puntos guía. Todo lee la paleta y las letras del editor (themes/carta.css).
--}}
@php
    $heroEyebrow = ($page->welcome['subtitulo'] ?? null) ?: ($invCopy['hero_eyebrow'] ?? 'Mis XV años');
    $heroMessage = $page->welcome['mensaje'] ?? '';
    $heroDay = ($page->welcome['fecha_texto'] ?? null)
        ?: \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('l j \d\e F'));
@endphp

<header id="inicio" class="inv-hero cb-hero">
    <div class="cb-card cb-card--cover">
        {{-- El ojal y la borla del cordón, que queda colgando sobre la tapa --}}
        <span class="cb-card__eyelet" aria-hidden="true"></span>
        <span class="cb-card__tassel" aria-hidden="true">
            <svg viewBox="0 0 40 110" focusable="false">
                <path class="cb-tassel-string" d="M20 0 C14 16 26 26 20 44"/>
                <ellipse class="cb-tassel-knot" cx="20" cy="46" rx="6" ry="5"/>
                <path class="cb-tassel-cap" d="M13 50 H27 L25 58 H15 Z"/>
                <path class="cb-tassel-fringe" d="M15 58 L11 104 M18 58 L16 106 M20 58 L20 107 M22 58 L24 106 M25 58 L29 104"/>
            </svg>
        </span>

        <p class="cb-card__title inv-fade-up">{{ $invCopy['card_title'] ?? 'Carta de baile' }}</p>
        <h1 class="cb-name inv-fade-up inv-fade-up--1">{{ $page->displayName }}</h1>
        <p class="cb-eyebrow inv-fade-up inv-fade-up--1">{{ $heroEyebrow }}</p>

        @if($page->heroImage)
            @php($heroSrcset = \App\Support\CloudinaryImage::srcset($page->heroImage, [480, 768, 1200]))
            <figure class="cb-photo inv-fade-up inv-fade-up--2">
                <span class="cb-photo__frame"><img
                    data-parallax="0.08"
                    src="{{ \App\Support\CloudinaryImage::url($page->heroImage, 1200) }}"
                    @if($heroSrcset) srcset="{{ $heroSrcset }}" sizes="(min-width: 640px) 18rem, 64vw" @endif
                    alt=""
                    loading="eager"
                    fetchpriority="high"
                    decoding="async"
                ></span>
                <i class="cb-photo__corner" aria-hidden="true"></i>
                <i class="cb-photo__corner" aria-hidden="true"></i>
                <i class="cb-photo__corner" aria-hidden="true"></i>
                <i class="cb-photo__corner" aria-hidden="true"></i>
            </figure>
        @endif

        {{-- En el enlace personal, el renglón lleva el nombre del invitado; en el general, una sola frase --}}
        <p class="cb-reserved inv-fade-up inv-fade-up--2">
            @if($guest)
                <span class="cb-reserved__label">{{ $invCopy['card_reserved'] ?? 'Reservado para' }}</span>
                <span class="cb-reserved__name">{{ $guest->name }}</span>
            @else
                <span class="cb-reserved__name">{{ $invCopy['card_reserved_any'] ?? 'Un lugar reservado para ti' }}</span>
            @endif
        </p>

        <dl class="cb-facts inv-fade-up inv-fade-up--3">
            <div class="cb-facts__row">
                <dt>{{ $invCopy['hero_day_label'] ?? 'Fecha' }}</dt>
                <span class="cb-leader" aria-hidden="true"></span>
                <dd>{{ $heroDay }}</dd>
            </div>
            <div class="cb-facts__row">
                <dt>{{ $invCopy['hero_time_label'] ?? 'Hora' }}</dt>
                <span class="cb-leader" aria-hidden="true"></span>
                <dd>{{ $page->eventDate->format('H:i') }}</dd>
            </div>
            @if($page->placeName)
                <div class="cb-facts__row">
                    <dt>{{ $invCopy['hero_place_label'] ?? 'Salón' }}</dt>
                    <span class="cb-leader" aria-hidden="true"></span>
                    <dd>{{ $page->placeName }}</dd>
                </div>
            @endif
        </dl>

        @if(!empty($heroMessage))
            <p class="cb-message inv-fade-up inv-fade-up--3">{{ $heroMessage }}</p>
        @endif
    </div>

    <a href="#contenido" class="inv-hero__scroll cb-hero__scroll">
        {{ $invCopy['scroll_hint'] ?? 'Desliza' }}
        <span class="inv-hero__scroll-line" aria-hidden="true"></span>
    </a>
</header>
