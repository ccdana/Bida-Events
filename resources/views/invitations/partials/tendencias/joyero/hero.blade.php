{{--
    Portada de «Joyero musical»: el joyero abierto, visto desde arriba. En el forro de la tapa, su nombre
    bordado; en la bandeja de terciopelo capitoné, la tiara y el relicario con la foto, colgado de su
    cadena. Debajo, la tarjeta de la joyería con el día, la hora y el lugar, y el mensaje. Al abrirse el
    joyero el estuche se asienta, el nombre se borda, sube la tiara y el relicario cae en su cadena y se
    mece; mientras suena la música se mece al compás y la tiara destella en cada tiempo del vals (al
    tocarlo, se mece de nuevo: data-poke). Estilos en css/invitation/tendencias/joyero.css.
--}}
@php
    $heroEyebrow = ($page->welcome['subtitulo'] ?? null) ?: ($invCopy['hero_eyebrow'] ?? 'Mis XV años');
    $heroMessage = $page->welcome['mensaje'] ?? '';
    $heroDay = ($page->welcome['fecha_texto'] ?? null)
        ?: \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('l j \d\e F'));
@endphp

<header id="inicio" class="inv-hero jo-hero">
    <p class="jo-kicker">{{ $heroEyebrow }}</p>

    <div class="jo-case">
        {{-- El forro de la tapa, con su nombre --}}
        <div class="jo-case__lid">
            <h1 class="jo-case__name">{{ $page->displayName }}</h1>
        </div>

        {{-- La bandeja: la tiara y el relicario en su cadena --}}
        <div class="jo-case__tray">
            {{-- La tiara, con un destello por tiempo del vals: la piedra del centro en el primero, las de los lados en el segundo y el tercero --}}
            <span class="jo-tiara-wrap" aria-hidden="true">
                <svg class="jo-tiara" viewBox="0 0 120 44" focusable="false">
                    <path d="M6 40 L12 16 L28 30 L44 10 L60 26 L76 10 L92 30 L108 16 L114 40 Z"/>
                    <path class="jo-tiara__band" d="M4 40 H116"/>
                    <circle cx="60" cy="24" r="4"/>
                    <circle cx="44" cy="8" r="2.6"/>
                    <circle cx="76" cy="8" r="2.6"/>
                    <circle cx="12" cy="14" r="2.2"/>
                    <circle cx="108" cy="14" r="2.2"/>
                </svg>
                <i class="jo-sparkle jo-sparkle--downbeat" style="--n: 0; --x: 50%; --y: 55%"></i>
                <i class="jo-sparkle" style="--n: 1; --x: 36.7%; --y: 18%"></i>
                <i class="jo-sparkle" style="--n: 2; --x: 63.3%; --y: 18%"></i>
            </span>
            <svg class="jo-chain" viewBox="0 0 200 60" preserveAspectRatio="none" aria-hidden="true" focusable="false">
                <path d="M4 2 C40 56 160 56 196 2"/>
            </svg>
            <figure class="jo-locket" data-poke="swing">
                <span class="jo-locket__loop" aria-hidden="true"></span>
                <span class="jo-locket__photo">
                    @include('invitations.partials.tendencias.photo', ['widths' => [480, 768, 1200], 'width' => 768, 'sizes' => '(min-width: 640px) 13rem, 50vw'])
                </span>
            </figure>
        </div>
    </div>

    {{-- La tarjeta de la joyería --}}
    <div class="jo-card">
        <p class="jo-card__title">{{ $invCopy['jewel_card'] ?? 'Mis XV años' }}</p>
        <dl class="jo-card__fields">
            <div>
                <dt>Día</dt>
                <dd>{{ $heroDay }}</dd>
            </div>
            <div>
                <dt>Hora</dt>
                <dd>{{ $page->eventDate->format('H:i') }}</dd>
            </div>
            @if($page->placeName)
                <div class="jo-card__wide">
                    <dt>Lugar</dt>
                    <dd>{{ $page->placeName }}</dd>
                </div>
            @endif
        </dl>
    </div>

    @if(!empty($heroMessage))
        <p class="jo-message">{{ $heroMessage }}</p>
    @endif

    <a href="#contenido" class="inv-hero__scroll jo-hero__scroll">
        {{ $invCopy['scroll_hint'] ?? 'Desliza' }}
        <span class="inv-hero__scroll-line" aria-hidden="true"></span>
    </a>
</header>
