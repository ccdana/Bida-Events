{{--
    Portada de «Álbum de stickers»: una página del álbum. Quien cumple es el sticker brillante, con su
    edad como número de colección; la fecha y la hora van pegadas en sus casillas y la tercera casilla
    está vacía: «Falta la tuya», que lleva a confirmar. El lugar es un sticker apaisado. Con el mouse el
    sticker brillante se inclina y el brillo lo sigue (stickerTilt, abajo). Desde los 18 años los
    stickers van derechos (.is-grown). Estilos en themes/stickers.css.
--}}
@php
    $age = $page->age();
    $grown = $age !== null && $age >= 18;
    $heroEyebrow = ($page->welcome['subtitulo'] ?? null) ?: ($invCopy['hero_eyebrow'] ?? '¡Celebremos juntos!');
    $heroMessage = $page->welcome['mensaje'] ?? '';
    $date = $page->eventDate->locale('es');
@endphp

<header id="inicio" @class(['inv-hero', 'st-hero', 'is-grown' => $grown])>
    <div class="st-page">
        <p class="st-page__banner inv-fade-up">{{ $heroEyebrow }}</p>
        <h1 class="st-page__name inv-fade-up inv-fade-up--1">{{ $page->displayName }}</h1>

        {{-- El sticker brillante en su casilla: el número de colección es la edad --}}
        <div class="st-slot st-slot--star">
            <article class="st-fig st-fig--foil" data-sticker
                x-data="stickerTilt()"
                @pointermove="move($event)"
                @pointerleave="leave()"
                :style="`--rx: ${rx}deg; --ry: ${ry}deg; --mx: ${mx}%`">
                <div class="st-fig__face st-fig__face--photo">
                    @if($page->heroImage)
                        @php($heroSrcset = \App\Support\CloudinaryImage::srcset($page->heroImage, [480, 768, 1080]))
                        <img
                            src="{{ \App\Support\CloudinaryImage::url($page->heroImage, 768) }}"
                            @if($heroSrcset) srcset="{{ $heroSrcset }}" sizes="(min-width: 640px) 20rem, 72vw" @endif
                            alt=""
                            loading="eager"
                            fetchpriority="high"
                            decoding="async"
                        >
                    @else
                        <span class="st-fig__initials">{{ $page->initials() }}</span>
                    @endif
                    <span class="st-fig__num">{{ $age ?? $page->initials() }}</span>
                </div>
                <p class="st-fig__band">
                    {{ $page->displayName }}
                    @if($age !== null)
                        <small>{{ $age }} {{ $invCopy['sticker_age'] ?? 'años' }}</small>
                    @endif
                </p>
                <span class="st-fig__shine" aria-hidden="true"></span>
            </article>
        </div>

        <ul class="st-row">
            <li class="st-slot">
                <div class="st-fig st-fig--mini" data-sticker>
                    <div class="st-fig__face">
                        <b class="st-fig__big">{{ $date->format('j') }}</b>
                        <span>{{ \Illuminate\Support\Str::ucfirst($date->translatedFormat('F')) }}</span>
                    </div>
                    <p class="st-fig__band">{{ \Illuminate\Support\Str::ucfirst($date->translatedFormat('l')) }}</p>
                </div>
            </li>
            <li class="st-slot">
                <div class="st-fig st-fig--mini st-fig--alt" data-sticker>
                    <div class="st-fig__face">
                        <b class="st-fig__big st-fig__big--time">{{ $page->eventDate->format('H:i') }}</b>
                    </div>
                    <p class="st-fig__band">{{ $invCopy['sticker_time'] ?? 'La hora' }}</p>
                </div>
            </li>
            <li class="st-slot st-slot--empty">
                {{-- La casilla que falta llenar: la del invitado, que se pega al confirmar --}}
                @if($page->showsRsvp())
                    <a href="#rsvp" class="st-missing">
                        <b aria-hidden="true">?</b>
                        <span>{{ $invCopy['sticker_missing'] ?? 'Falta la tuya' }}</span>
                        <strong>{{ $invCopy['sticker_going'] ?? '¡Voy!' }}</strong>
                    </a>
                @else
                    <span class="st-missing" aria-hidden="true"><b>?</b></span>
                @endif
            </li>
        </ul>

        @if($page->placeName)
            <div class="st-slot st-slot--wide">
                <div class="st-fig st-fig--wide" data-sticker>
                    <div class="st-fig__face">
                        <p class="st-fig__place">{{ $page->placeName }}</p>
                    </div>
                    <p class="st-fig__band">{{ $invCopy['sticker_place'] ?? 'El lugar' }}</p>
                </div>
            </div>
        @endif
    </div>

    @if(!empty($heroMessage))
        <p class="st-message">{{ $heroMessage }}</p>
    @endif

    <a href="#contenido" class="inv-hero__scroll st-hero__scroll">
        {{ $invCopy['scroll_hint'] ?? 'Desliza' }}
        <span class="inv-hero__scroll-line" aria-hidden="true"></span>
    </a>
</header>

<script>
// El sticker brillante se inclina hacia el mouse y el reflejo lo sigue; en el celular brilla solo
function stickerTilt() {
    return {
        rx: 0,
        ry: 0,
        mx: 50,
        move(event) {
            if (event.pointerType !== 'mouse' || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }

            const box = this.$el.getBoundingClientRect();
            const x = (event.clientX - box.left) / box.width;
            const y = (event.clientY - box.top) / box.height;

            this.ry = ((x - 0.5) * 16).toFixed(2);
            this.rx = ((0.5 - y) * 12).toFixed(2);
            this.mx = Math.round(x * 100);
        },
        leave() {
            this.rx = 0;
            this.ry = 0;
            this.mx = 50;
        },
    };
}
</script>
