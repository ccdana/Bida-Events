{{--
    Acto I · El reflejo: la luna solo se ve en el agua. Nombres, el día en que se conocieron, la
    primera foto (vista como a través del agua) y las primeras impresiones.
    Datos: bienvenida (nombres, subtítulo, foto), juntos_desde (fecha), relato.primeras_impresiones.
--}}
@php
    $actIntro = $filled($page->welcome['subtitulo'] ?? null) ?? $invCopy['act1_intro_fallback'];
    $firstImpression = $filled($story['primeras_impresiones'] ?? null) ?? $invCopy['act1_first_fallback'];
@endphp

<header class="story-act story-act--1" id="inicio" data-act>
    <div class="story-opening">
        <p class="story-opening__title">{{ $invCopy['act1_title'] }}</p>
        <h1 class="story-names">
            @foreach($names as $name)
                @if(! $loop->first)
                    <span class="story-names__and">&amp;</span>
                @endif
                <span class="story-names__name">{{ $name }}</span>
            @endforeach
        </h1>
        <p class="story-opening__intro">{{ $actIntro }}</p>
        @if($cardTo !== '')
            <p class="story-opening__to">Para {{ $cardTo }}</p>
        @endif
    </div>

    <div class="story-act__body">
        @include('invitations.partials.historia.mark', ['number' => 'I', 'name' => $invCopy['act1_label']])

        <p class="story-met reveal" id="juntos-desde">
            @if($metOn)
                {{ $invCopy['act1_met_prefix'] }}
                <time datetime="{{ $metOn->toDateString() }}">{{ $metOn->translatedFormat('j \d\e F \d\e Y') }}</time>
            @else
                {{ $invCopy['act1_met_fallback'] }}
            @endif
        </p>

        @if($page->heroImage)
            <figure class="story-underwater reveal">
                <img src="{{ \App\Support\CloudinaryImage::url($page->heroImage, 800) }}"
                    @if($srcset = \App\Support\CloudinaryImage::srcset($page->heroImage, [480, 800])) srcset="{{ $srcset }}" sizes="(min-width: 640px) 26rem, 82vw" @endif
                    alt="{{ $filled($page->welcome['imagen_hero_alt'] ?? null) ?? 'Primera foto de '.$coupleLabel }}"
                    width="800" height="1000" decoding="async" fetchpriority="high">
                <span class="story-underwater__ripples" aria-hidden="true"></span>
            </figure>
        @endif

        <div class="story-copy reveal">
            @foreach($paragraphs($firstImpression) as $paragraph)
                <p>{!! nl2br(e($paragraph)) !!}</p>
            @endforeach
        </div>
    </div>
</header>
