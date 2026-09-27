{{--
    Portada de «La gota»: una gota grande suspendida con la foto adentro, como vista a través del agua
    (borde de vidrio, brillo y su sombra). Debajo, el agua donde va a caer: anillos que se abren
    despacio, una gotita que cae de vez en cuando y, a lo largo del primer anillo, los padrinos. El
    nombre se refleja en el agua. Estilos en themes/gota.css.
--}}
@php
    $heroEyebrow = ($page->welcome['subtitulo'] ?? null) ?: ($invCopy['hero_eyebrow'] ?? 'Mi bautizo');
    $heroMessage = $page->welcome['mensaje'] ?? '';
    $heroDay = ($page->welcome['fecha_texto'] ?? null)
        ?: \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('l j \d\e F'));

    // Los padrinos principales (los primeros de la lista) van escritos sobre el primer anillo; la letra se achica para que quepan en la curva
    $sponsors = $page->visible('destacados')
        ? (string) collect($modulos['destacados']['padrinos'] ?? [])->pluck('nombres')->filter()->first()
        : '';
    $ringText = $sponsors !== '' ? ($invCopy['ring_label'] ?? 'Mis padrinos').':  '.$sponsors : '';
    $ringSize = $ringText !== '' ? min(13, round(300 / max(1, mb_strlen($ringText) * 0.54), 1)) : 0;
    $ringId = 'gt-ring-'.\Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(5));
@endphp

<header id="inicio" class="inv-hero gt-hero">
    <p class="gt-kicker inv-fade-up">{{ $heroEyebrow }}</p>

    <div class="gt-scene inv-fade-up inv-fade-up--1">
        {{-- Una gotita que cae de vez en cuando al agua, al costado --}}
        <span class="gt-scene__drip" aria-hidden="true"></span>

        <div class="gt-bead">
            <div class="gt-bead__lens">
                @include('invitations.partials.tendencias.photo', ['widths' => [480, 768], 'width' => 768, 'sizes' => '(min-width: 640px) 18rem, 64vw'])
            </div>
            <svg class="gt-bead__glass" viewBox="0 0 100 124" aria-hidden="true" focusable="false">
                <path class="gt-bead__edge" d="M50 2 C58 22 96 44 96 76 A46 46 0 0 1 4 76 C4 44 42 22 50 2 Z"/>
                <path class="gt-bead__rim" d="M50 7 C57 25 91 46 91 76 A41 41 0 0 1 9 76 C9 46 43 25 50 7 Z"/>
                <path class="gt-bead__glint" d="M22 70 C21 55 30 40 40 28"/>
                <path class="gt-bead__glint gt-bead__glint--low" d="M70 108 C78 103 84 95 86 86"/>
                <circle class="gt-bead__spark" cx="27" cy="84" r="2.6"/>
            </svg>
        </div>

        <svg class="gt-water" viewBox="0 0 400 120" focusable="false" @if($ringText === '') aria-hidden="true" @endif>
            <defs>
                <path id="{{ $ringId }}" d="M58 44 A142 36 0 0 0 342 44"/>
            </defs>
            <ellipse class="gt-water__shadow" cx="200" cy="40" rx="70" ry="12"/>
            @foreach([0, 1, 2] as $wave)
                <ellipse class="gt-water__wave" style="--w: {{ $wave }}" cx="200" cy="44" rx="190" ry="48"/>
            @endforeach
            <ellipse class="gt-water__ring" cx="200" cy="44" rx="142" ry="36"/>
            <ellipse class="gt-water__ring gt-water__ring--far" cx="200" cy="44" rx="186" ry="47"/>
            {{-- La gotita del costado y su onda --}}
            <ellipse class="gt-water__drip-ring" cx="334" cy="84" rx="26" ry="6"/>
            @if($ringText !== '')
                <text class="gt-water__text" style="font-size: {{ $ringSize }}px">
                    <textPath href="#{{ $ringId }}" startOffset="50%" text-anchor="middle">{{ $ringText }}</textPath>
                </text>
            @endif
        </svg>
    </div>

    <div class="gt-title inv-fade-up inv-fade-up--2">
        <h1 class="gt-name">{{ $page->displayName }}</h1>
        {{-- Su reflejo en el agua --}}
        <p class="gt-name gt-name--reflection" aria-hidden="true">{{ $page->displayName }}</p>
    </div>

    <ul class="gt-details inv-fade-up inv-fade-up--3">
        <li>{{ $heroDay }}</li>
        <li>{{ $page->eventDate->format('H:i') }}</li>
        @if($page->placeName)
            <li class="gt-details__place">{{ $page->placeName }}</li>
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
