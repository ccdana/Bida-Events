{{--
    Portada de «Próxima salida»: el panel de salidas de una terminal. El nombre aparece en casillas
    de paletas que giran una tras otra; la carrera o el colegio es la franja del destino; la fecha,
    la hora, el lugar (la puerta) y el estado van en filas del tablero. La foto se ve por la
    ventanilla del avión. Estilos en themes/salidas.css.
--}}
@php
    $heroStripe = ($page->welcome['subtitulo'] ?? null) ?: ($invCopy['hero_eyebrow'] ?? 'Me gradúo');
    $heroMessage = $page->welcome['mensaje'] ?? '';
    $heroDay = ($page->welcome['fecha_texto'] ?? null)
        ?: \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('D j M'));
    $status = $page->eventDate->isToday() ? ($invCopy['board_today'] ?? 'Embarcando hoy') : ($invCopy['board_on_time'] ?? 'A tiempo');
    $words = preg_split('/\s+/u', trim($page->displayName)) ?: [$page->displayName];
    $flap = 0;
@endphp

<header id="inicio" class="inv-hero ps-hero">
    <div class="ps-board inv-fade-up">
        <div class="ps-board__top">
            <p class="ps-board__title"><span class="ps-light" aria-hidden="true"></span>{{ $invCopy['board_title'] ?? 'Próxima salida' }}</p>
            <p class="ps-board__clock">{{ $page->eventDate->format('H:i') }}</p>
        </div>

        {{-- Cada letra en su casilla; el nombre completo lo lee el lector de pantalla --}}
        <h1 class="ps-flaps">
            <span class="sr-only">{{ $page->displayName }}</span>
            @foreach($words as $word)
                <span class="ps-flaps__word" style="--len: {{ max(mb_strlen($word), 7) }}" aria-hidden="true">
                    @foreach(mb_str_split($word) as $char)
                        <span class="ps-flap" style="--i: {{ $flap++ }}">{{ $char }}</span>
                    @endforeach
                </span>
            @endforeach
        </h1>

        <p class="ps-stripe">{{ $heroStripe }}</p>

        <dl class="ps-rows">
            <div class="ps-row">
                <dt>{{ $invCopy['board_date'] ?? 'Fecha' }}</dt>
                <dd>{{ $heroDay }}</dd>
            </div>
            <div class="ps-row">
                <dt>{{ $invCopy['board_time'] ?? 'Hora' }}</dt>
                <dd>{{ $page->eventDate->format('H:i') }}</dd>
            </div>
            @if($page->placeName)
                <div class="ps-row ps-row--wide">
                    <dt>{{ $invCopy['board_gate'] ?? 'Puerta' }}</dt>
                    <dd>{{ $page->placeName }}</dd>
                </div>
            @endif
            <div class="ps-row ps-row--status">
                <dt>{{ $invCopy['board_status'] ?? 'Estado' }}</dt>
                <dd>{{ $status }}</dd>
            </div>
        </dl>
    </div>

    @if($page->heroImage)
        @php($heroSrcset = \App\Support\CloudinaryImage::srcset($page->heroImage, [480, 768, 1200]))
        <figure class="ps-window inv-fade-up inv-fade-up--2">
            <span class="ps-window__view"><img
                data-parallax="0.1"
                src="{{ \App\Support\CloudinaryImage::url($page->heroImage, 1200) }}"
                @if($heroSrcset) srcset="{{ $heroSrcset }}" sizes="(min-width: 640px) 18rem, 62vw" @endif
                alt=""
                loading="eager"
                fetchpriority="high"
                decoding="async"
            ></span>
        </figure>
    @endif

    @if(!empty($heroMessage))
        <p class="ps-message inv-fade-up inv-fade-up--3">{{ $heroMessage }}</p>
    @endif

    <a href="#contenido" class="inv-hero__scroll ps-hero__scroll">
        {{ $invCopy['scroll_hint'] ?? 'Desliza' }}
        <span class="inv-hero__scroll-line" aria-hidden="true"></span>
    </a>
</header>
