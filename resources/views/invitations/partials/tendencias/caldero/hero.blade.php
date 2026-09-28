{{--
    Portada de «Caldero encantado»: el frasco de la poción, con su corcho y un hilo que sostiene la
    etiqueta de la receta. Adentro del frasco va la foto; abajo, un dedo de poción que burbujea y el
    brillo del vidrio. Debajo, el nombre como humo de colores, los ingredientes de la receta, cuándo
    se sirve, el lugar y el mensaje. Estilos en css/invitation/tendencias/caldero.css.
--}}
@php
    $heroKicker = ($page->welcome['subtitulo'] ?? null) ?: ($invCopy['intro_eyebrow'] ?? 'Se está preparando una fiesta');
    $heroMessage = $page->welcome['mensaje'] ?? '';
    $heroDay = ($page->welcome['fecha_texto'] ?? null)
        ?: \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('l j \d\e F'));
@endphp

<header id="inicio" class="inv-hero cl-hero">
    <p class="cl-kicker inv-fade-up">{{ $heroKicker }}</p>

    <div class="cl-jar inv-fade-up inv-fade-up--1">
        <span class="cl-jar__cork" aria-hidden="true"></span>
        <span class="cl-jar__neck" aria-hidden="true"></span>
        <div class="cl-jar__body">
            <span class="cl-jar__photo">
                @include('invitations.partials.tendencias.photo', ['widths' => [480, 768], 'width' => 768, 'sizes' => '(min-width: 640px) 19rem, 74vw'])
            </span>
            <span class="cl-jar__liquid" aria-hidden="true">
                @for($bubble = 0; $bubble < 7; $bubble++)
                    <i style="--i: {{ $bubble }}"></i>
                @endfor
            </span>
            <span class="cl-jar__shine" aria-hidden="true"></span>
        </div>
        {{-- La etiqueta de la receta, atada al cuello con un hilo --}}
        <span class="cl-jar__tag">
            <span class="cl-jar__tag-label">{{ $invCopy['potion_label'] ?? 'Receta secreta' }}</span>
            <span class="cl-jar__tag-number">N.º {{ $page->eventDate->format('d') }}</span>
        </span>
    </div>

    <h1 class="cl-name inv-fade-up inv-fade-up--2">{{ $page->displayName }}</h1>

    @if(!empty($invCopy['potion_ingredients']))
        <p class="cl-ingredients inv-fade-up inv-fade-up--3">{{ $invCopy['potion_ingredients'] }}</p>
    @endif

    <div class="cl-when inv-fade-up inv-fade-up--3">
        <span class="cl-when__item">{{ $heroDay }}</span>
        <span class="cl-when__item">{{ $page->eventDate->format('H:i') }}</span>
    </div>

    @if($page->placeName)
        <p class="cl-place inv-fade-up inv-fade-up--3">{{ $page->placeName }}</p>
    @endif

    @if(!empty($heroMessage))
        <p class="cl-message inv-fade-up inv-fade-up--3">{{ $heroMessage }}</p>
    @endif

    <a href="#contenido" class="inv-hero__scroll cl-hero__scroll">
        {{ $invCopy['scroll_hint'] ?? 'Desliza' }}
        <span class="inv-hero__scroll-line" aria-hidden="true"></span>
    </a>
</header>
