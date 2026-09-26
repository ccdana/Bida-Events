{{--
    Portada de «La gota»: ondas concéntricas con la foto (o las iniciales) en el centro. Los padrinos
    rodean el primer anillo con su nombre escrito sobre la onda; más abajo, el nombre y cada dato
    separado por una onda. Estilos en themes/gota.css.
--}}
@php
    $heroEyebrow = ($page->welcome['subtitulo'] ?? null) ?: ($invCopy['hero_eyebrow'] ?? 'Mi bautizo');
    $heroMessage = $page->welcome['mensaje'] ?? '';
    $heroDay = ($page->welcome['fecha_texto'] ?? null)
        ?: \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('l j \d\e F'));

    // Los padrinos alrededor del anillo; el tamaño de la letra se ajusta para que den la vuelta justa
    $sponsors = $page->visible('destacados')
        ? collect($modulos['destacados']['padrinos'] ?? [])->pluck('nombres')->filter()->take(3)->implode('  ◦  ')
        : '';
    $ringText = $sponsors !== '' ? ($invCopy['ring_label'] ?? 'Mis padrinos').':  '.$sponsors : '';
    $ringSize = $ringText !== '' ? min(15, round(720 / max(1, mb_strlen($ringText) * 0.56), 1)) : 0;
@endphp

<header id="inicio" class="inv-hero gt-hero">
    <div class="gt-pool inv-fade-up">
        <svg class="gt-rings" viewBox="0 0 400 400" aria-hidden="true" focusable="false">
            @foreach([112, 138, 164, 190] as $ring => $radius)
                <circle cx="200" cy="200" r="{{ $radius }}" style="--r: {{ $ring }}"/>
            @endforeach
        </svg>

        @if($ringText !== '')
            <svg class="gt-ring-text" viewBox="0 0 400 400" focusable="false">
                <defs>
                    <path id="gt-ring-path" d="M200 200 m-124 0 a124 124 0 1 1 248 0 a124 124 0 1 1 -248 0"/>
                </defs>
                <text style="font-size: {{ $ringSize }}px"><textPath href="#gt-ring-path" startOffset="2%">{{ $ringText }}</textPath></text>
            </svg>
        @endif

        <figure class="gt-center">
            @if($page->heroImage)
                @php($heroSrcset = \App\Support\CloudinaryImage::srcset($page->heroImage, [480, 768]))
                <img
                    data-parallax="0.08"
                    src="{{ \App\Support\CloudinaryImage::url($page->heroImage, 768) }}"
                    @if($heroSrcset) srcset="{{ $heroSrcset }}" sizes="12rem" @endif
                    alt=""
                    loading="eager"
                    fetchpriority="high"
                    decoding="async"
                >
            @else
                <span class="gt-center__initials">{{ $page->initials() }}</span>
            @endif
        </figure>
    </div>

    <p class="gt-eyebrow inv-fade-up inv-fade-up--1">{{ $heroEyebrow }}</p>
    <h1 class="gt-name inv-fade-up inv-fade-up--1">{{ $page->displayName }}</h1>

    <ul class="gt-details inv-fade-up inv-fade-up--2">
        <li>{{ $heroDay }}</li>
        <li>{{ $page->eventDate->format('H:i') }}</li>
        @if($page->placeName)
            <li>{{ $page->placeName }}</li>
        @endif
    </ul>

    @if(!empty($heroMessage))
        <p class="gt-message inv-fade-up inv-fade-up--3">{{ $heroMessage }}</p>
    @endif

    <a href="#contenido" class="inv-hero__scroll gt-hero__scroll">
        {{ $invCopy['scroll_hint'] ?? 'Desliza' }}
        <span class="inv-hero__scroll-line" aria-hidden="true"></span>
    </a>
</header>
