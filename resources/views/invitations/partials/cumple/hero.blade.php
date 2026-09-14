{{-- Portada del cumpleaños: banderines, la edad gigante detrás de una foto tipo sticker, globos, nombre que salta y entrada de fiesta --}}
@php
    $heroRibbon = ($page->welcome['subtitulo'] ?? null) ?: ($invCopy['hero_eyebrow'] ?? '¡Celebremos juntos!');
    $heroMessage = $page->welcome['mensaje'] ?? '';
    $heroDay = ($page->welcome['fecha_texto'] ?? null)
        ?: \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('l j \d\e F'));
    $heroAge = $page->age();
    $letterIndex = 0;
@endphp

<header id="inicio" class="inv-hero inv-cumple-hero">
    @include('invitations.partials.cumple.bunting', ['class' => 'inv-cumple-hero__bunting'])

    <div class="inv-cumple-hero__inner">
        <p class="inv-cumple-ribbon inv-cumple-pop" style="--d: 0.1s">{{ $heroRibbon }}</p>

        <div class="inv-cumple-stage">
            @if($heroAge)
                <span class="inv-cumple-age" aria-hidden="true">{{ $heroAge }}</span>
            @endif

            <figure class="inv-cumple-photo inv-cumple-pop" style="--d: 0.3s">
                @if($page->heroImage)
                    @php($heroSrcset = \App\Support\CloudinaryImage::srcset($page->heroImage, [480, 768, 1200]))
                    <img
                        src="{{ \App\Support\CloudinaryImage::url($page->heroImage, 1200) }}"
                        @if($heroSrcset) srcset="{{ $heroSrcset }}" sizes="(min-width: 640px) 14rem, 55vw" @endif
                        alt=""
                        loading="eager"
                        fetchpriority="high"
                        decoding="async"
                    >
                @else
                    <span class="inv-cumple-photo__initial">{{ $page->initials() }}</span>
                @endif
            </figure>

            @include('invitations.partials.cumple.balloon', ['class' => 'inv-cumple-stage__balloon inv-cumple-stage__balloon--1'])
            @include('invitations.partials.cumple.balloon', ['class' => 'inv-cumple-stage__balloon inv-cumple-stage__balloon--2'])
            @include('invitations.partials.cumple.balloon', ['class' => 'inv-cumple-stage__balloon inv-cumple-stage__balloon--3'])
        </div>

        <h1 class="inv-cumple-hero__name" aria-label="{{ $page->displayName }}">
            @foreach(preg_split('/\s+/u', trim($page->displayName)) as $word)
                <span class="inv-cumple-word" aria-hidden="true">@foreach(mb_str_split($word) as $letter)<span class="inv-cumple-letter" style="--i: {{ $letterIndex++ }}">{{ $letter }}</span>@endforeach</span>
            @endforeach
        </h1>

        @if(!empty($heroMessage))
            <p class="inv-cumple-hero__message inv-cumple-pop" style="--d: 0.8s">{{ $heroMessage }}</p>
        @endif

        <div class="inv-cumple-ticket inv-cumple-pop" style="--d: 0.95s">
            <span class="inv-cumple-ticket__label">Fiesta</span>
            <span class="inv-cumple-ticket__main">
                <span class="inv-cumple-ticket__day">{{ $heroDay }}</span>
                <span class="inv-cumple-ticket__year">{{ $page->eventDate->format('Y') }}</span>
            </span>
            <span class="inv-cumple-ticket__stub">{{ $page->eventDate->format('H:i') }}</span>
        </div>

        <a href="#contenido" class="inv-hero__scroll inv-cumple-hero__scroll">
            Desliza
            <span class="inv-hero__scroll-line" aria-hidden="true"></span>
        </a>
    </div>
</header>
