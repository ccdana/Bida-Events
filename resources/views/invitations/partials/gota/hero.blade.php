{{--
    Portada de «La gota»: arriba el aire, con el nombre; desde ahí cae una gota cada tanto. Abajo, el
    agua profunda: la foto en el centro de la pileta, donde cae la gota, y las ondas que se abren a su
    alrededor en cada golpe. La orilla es una ola que se mueve despacio. En el agua van los padrinos
    principales, la fecha, la hora, el lugar y el mensaje. Estilos en themes/gota.css.
--}}
@php
    $heroEyebrow = ($page->welcome['subtitulo'] ?? null) ?: ($invCopy['hero_eyebrow'] ?? 'Mi bautizo');
    $heroMessage = $page->welcome['mensaje'] ?? '';
    $heroDay = ($page->welcome['fecha_texto'] ?? null)
        ?: \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('l j \d\e F'));
    // Los padrinos principales (los primeros de la lista) van en el agua, bajo la foto
    $sponsors = $page->visible('destacados')
        ? (string) collect($modulos['destacados']['padrinos'] ?? [])->pluck('nombres')->filter()->first()
        : '';
@endphp

<header id="inicio" class="inv-hero gt-hero">
    <div class="gt-hero__air">
        <p class="gt-kicker inv-fade-up">{{ $heroEyebrow }}</p>
        <h1 class="gt-name inv-fade-up inv-fade-up--1">{{ $page->displayName }}</h1>
        {{-- La gota que cae del nombre al agua --}}
        <span class="gt-hero__fall" aria-hidden="true"><i></i></span>
    </div>

    <div class="gt-hero__water">
        <div class="gt-pool inv-fade-up inv-fade-up--2">
            @foreach([0, 1, 2, 3] as $ring)
                <span class="gt-pool__ring" style="--r: {{ $ring }}" aria-hidden="true"></span>
            @endforeach
            <div class="gt-pool__photo">
                @include('invitations.partials.tendencias.photo', ['widths' => [480, 768], 'width' => 768, 'sizes' => '(min-width: 640px) 17rem, 60vw'])
            </div>
        </div>

        @if($sponsors !== '')
            <p class="gt-hero__sponsors inv-fade-up inv-fade-up--3">
                <span>{{ $invCopy['ring_label'] ?? 'Mis padrinos' }}</span>
                {{ $sponsors }}
            </p>
        @endif

        <p class="gt-when inv-fade-up inv-fade-up--3">
            <span>{{ $heroDay }}</span>
            <span class="gt-when__drop" aria-hidden="true"></span>
            <span>{{ $page->eventDate->format('H:i') }}</span>
        </p>
        @if($page->placeName)
            <p class="gt-place inv-fade-up inv-fade-up--3">{{ $page->placeName }}</p>
        @endif

        @if(!empty($heroMessage))
            <p class="gt-message inv-fade-up inv-fade-up--3">{{ $heroMessage }}</p>
        @endif

        <a href="#contenido" class="inv-hero__scroll gt-hero__scroll">
            {{ $invCopy['scroll_hint'] ?? 'Desliza' }}
            <span class="inv-hero__scroll-line" aria-hidden="true"></span>
        </a>
    </div>
</header>
