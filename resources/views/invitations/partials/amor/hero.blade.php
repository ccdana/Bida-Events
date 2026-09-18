{{--
    Portada de la tarjeta de amor: la foto de los dos como una instantánea que se revela sola
    (de marrón claro a la imagen), sujeta con cinta de papel, con florecitas que brotan alrededor
    (al tocarlas giran y sueltan pétalos) y «Para …» en letra manuscrita. Si no hay foto, queda el texto.
    En modo historia cada parte entra en su turno (data-step) y la foto se inclina con el teléfono.
    Aquí vive la primera de las tres mariposas escondidas (partials/amor/butterfly).
--}}
@php
    $heroPhrase = ($page->welcome['subtitulo'] ?? null) ?: ($invCopy['hero_eyebrow'] ?? 'Feliz Día del Amor');
    $heroMessage = trim((string) ($page->welcome['mensaje'] ?? ''));
    $heroImageAlt = ($page->welcome['imagen_hero_alt'] ?? null) ?: ($cardTo !== '' ? 'Foto para '.$cardTo : '');
    // Florecitas alrededor de la foto: posición, tamaño, giro y turno
    $sprouts = [
        ['x' => '-8%', 'y' => '12%', 'size' => '2.6rem', 'turn' => '-14deg', 'tone' => 'rose'],
        ['x' => '96%', 'y' => '4%', 'size' => '2.1rem', 'turn' => '18deg', 'tone' => 'daisy'],
        ['x' => '-6%', 'y' => '78%', 'size' => '2rem', 'turn' => '8deg', 'tone' => 'daisy'],
        ['x' => '94%', 'y' => '70%', 'size' => '2.8rem', 'turn' => '-6deg', 'tone' => 'sun'],
        ['x' => '102%', 'y' => '40%', 'size' => '1.6rem', 'turn' => '0deg', 'tone' => 'rose'],
    ];
@endphp

<header id="inicio" class="inv-amor-hero">
    <div class="inv-amor-hero__inner">
        <p class="inv-amor-hero__phrase inv-fade-up" data-step style="--step: 0">{{ $heroPhrase }}</p>

        @if($page->heroImage)
            @php($heroSrcset = \App\Support\CloudinaryImage::srcset($page->heroImage, [480, 768, 1200]))
            <div class="inv-amor-hero__frame">
                <figure class="inv-amor-hero__photo inv-fade-up inv-fade-up--1" data-step style="--step: 1">
                    <span class="inv-amor-hero__tape" aria-hidden="true"></span>
                    <span class="inv-amor-hero__film">
                        <img src="{{ \App\Support\CloudinaryImage::url($page->heroImage, 900) }}"
                            @if($heroSrcset) srcset="{{ $heroSrcset }}" sizes="(min-width: 768px) 24rem, 78vw" @endif
                            alt="{{ $heroImageAlt }}" loading="eager" fetchpriority="high" decoding="async" draggable="false">
                    </span>
                    <span class="inv-amor-hero__glare" aria-hidden="true"></span>
                    <figcaption class="inv-amor-hero__caption">{{ $invCopy['photo_caption'] ?? 'Tú y yo' }}</figcaption>
                </figure>

                @foreach($sprouts as $sprout)
                    <span class="inv-amor-sprout inv-amor-sprout--{{ $sprout['tone'] }}" data-story-ignore aria-hidden="true"
                        style="--x: {{ $sprout['x'] }}; --y: {{ $sprout['y'] }}; --size: {{ $sprout['size'] }}; --turn: {{ $sprout['turn'] }}; --i: {{ $loop->index }}"
                        x-data @click="$el.classList.remove('is-spun'); void $el.offsetWidth; $el.classList.add('is-spun'); $dispatch('inv-celebrate', { rect: $el.getBoundingClientRect(), amount: 8 })">
                        <svg viewBox="0 0 40 40"><use href="#amor-flower" /></svg>
                    </span>
                @endforeach
            </div>
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

    @include('invitations.partials.amor.butterfly', ['butterflyId' => 'portada', 'butterflyStyle' => '--bx: 82%; --by: 16%; --delay: 2.4s'])
</header>
