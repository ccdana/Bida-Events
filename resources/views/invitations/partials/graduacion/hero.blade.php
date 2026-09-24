{{--
    Portada de la graduación: la foto en un arco con doble filete dorado y un birrete apoyado en la
    esquina, el nombre en letra de firma, la carrera o el colegio como cinta y la fecha como el pie
    de un diploma (día, hora y «Promoción» con el año).
    Al abrirse: la cinta se despliega, el filete dorado se dibuja, la foto se revela, el birrete cae
    en su esquina, un brillo recorre el nombre y el pie del diploma se traza (themes/graduacion.css).
--}}
@php
    $heroRibbon = ($page->welcome['subtitulo'] ?? null) ?: ($invCopy['hero_eyebrow'] ?? 'Me gradúo');
    $heroMessage = $page->welcome['mensaje'] ?? '';
    $heroDay = ($page->welcome['fecha_texto'] ?? null)
        ?: \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('l j \d\e F'));
@endphp

<header id="inicio" class="inv-hero inv-grad-hero">
    <div class="inv-grad-hero__inner">
        <p class="inv-grad-ribbon inv-fade-up">{{ $heroRibbon }}</p>

        <div class="inv-grad-arch inv-fade-up inv-fade-up--1">
            {{-- Luz dorada que gira despacio detrás del arco --}}
            <span class="inv-grad-arch__glow" aria-hidden="true"></span>
            <figure class="inv-grad-arch__frame">
                @if($page->heroImage)
                    @php($heroSrcset = \App\Support\CloudinaryImage::srcset($page->heroImage, [480, 768, 1200]))
                    <img
                        src="{{ \App\Support\CloudinaryImage::url($page->heroImage, 1200) }}"
                        @if($heroSrcset) srcset="{{ $heroSrcset }}" sizes="(min-width: 640px) 18rem, 70vw" @endif
                        alt=""
                        loading="eager"
                        fetchpriority="high"
                        decoding="async"
                    >
                @else
                    <span class="inv-grad-arch__initials">{{ $page->initials() }}</span>
                @endif
            </figure>
            {{-- El doble filete dorado se dibuja alrededor del arco --}}
            <svg class="inv-grad-arch__filet" viewBox="0 0 100 125" preserveAspectRatio="none" aria-hidden="true" focusable="false">
                <path class="inv-grad-arch__line" pathLength="1" d="M1.9 123.1 V50 A48.1 48.1 0 0 1 98.1 50 V123.1 Z"/>
                <path class="inv-grad-arch__line inv-grad-arch__line--outer" pathLength="1" d="M0.5 124.5 V50 A49.5 49.5 0 0 1 99.5 50 V124.5 Z"/>
            </svg>
            @include('invitations.partials.graduacion.cap', ['class' => 'inv-grad-arch__cap'])
            @foreach([[-6, 18, 12, '0s'], [98, 42, 9, '-1.2s'], [4, 78, 8, '-2.1s'], [90, 8, 7, '-0.6s']] as [$sparkLeft, $sparkTop, $sparkSize, $sparkDelay])
                <span class="inv-grad-spark" style="--l: {{ $sparkLeft }}%; --t: {{ $sparkTop }}%; --s: {{ $sparkSize }}px; --d: {{ $sparkDelay }}" aria-hidden="true"></span>
            @endforeach
        </div>

        <h1 class="inv-grad-hero__name inv-fade-up inv-fade-up--2">{{ $page->displayName }}</h1>

        @if(!empty($heroMessage))
            <p class="inv-grad-hero__message inv-fade-up inv-fade-up--2">{{ $heroMessage }}</p>
        @endif

        <dl class="inv-grad-diploma inv-fade-up inv-fade-up--3">
            <div>
                <dt>{{ $invCopy['hero_day_label'] ?? 'Día' }}</dt>
                <dd>{{ $heroDay }}</dd>
            </div>
            <div>
                <dt>{{ $invCopy['hero_time_label'] ?? 'Hora' }}</dt>
                <dd>{{ $page->eventDate->format('H:i') }}</dd>
            </div>
            <div>
                <dt>{{ $invCopy['hero_class_label'] ?? 'Promoción' }}</dt>
                <dd>{{ $page->eventDate->format('Y') }}</dd>
            </div>
        </dl>
    </div>

    <a href="#contenido" class="inv-hero__scroll inv-grad-hero__scroll">
        {{ $invCopy['scroll_hint'] ?? 'Desliza' }}
        <span class="inv-hero__scroll-line" aria-hidden="true"></span>
    </a>
</header>
