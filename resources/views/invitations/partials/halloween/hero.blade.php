{{--
    Portada de Halloween: la luna llena (con la foto adentro, si la hay) y murciélagos que la cruzan,
    el nombre de la fiesta con brillo de vela, el mensaje, la entrada con la noche y la hora, y una
    loma con calabazas encendidas al pie.
--}}
@php
    $heroEyebrow = ($page->welcome['subtitulo'] ?? null) ?: ($invCopy['hero_eyebrow'] ?? 'Fiesta de Halloween');
    $heroMessage = $page->welcome['mensaje'] ?? '';
    $heroDay = ($page->welcome['fecha_texto'] ?? null)
        ?: \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('l j \d\e F'));
@endphp

<header id="inicio" class="inv-hero inv-hw-hero">
    <div class="inv-hw-hero__inner">
        <div class="inv-hw-moon inv-fade-up">
            <figure class="inv-hw-moon__disc">
                @if($page->heroImage)
                    @php($heroSrcset = \App\Support\CloudinaryImage::srcset($page->heroImage, [480, 768, 1200]))
                    <img
                        src="{{ \App\Support\CloudinaryImage::url($page->heroImage, 1200) }}"
                        @if($heroSrcset) srcset="{{ $heroSrcset }}" sizes="(min-width: 640px) 17rem, 64vw" @endif
                        alt=""
                        loading="eager"
                        fetchpriority="high"
                        decoding="async"
                    >
                @else
                    <span class="inv-hw-moon__crater" style="--x: 28%; --y: 34%; --s: 18%"></span>
                    <span class="inv-hw-moon__crater" style="--x: 60%; --y: 58%; --s: 24%"></span>
                    <span class="inv-hw-moon__crater" style="--x: 64%; --y: 22%; --s: 11%"></span>
                @endif
            </figure>
            @include('invitations.partials.halloween.bat', ['class' => 'inv-hw-moon__bat inv-hw-moon__bat--1'])
            @include('invitations.partials.halloween.bat', ['class' => 'inv-hw-moon__bat inv-hw-moon__bat--2'])
            @include('invitations.partials.halloween.bat', ['class' => 'inv-hw-moon__bat inv-hw-moon__bat--3'])
        </div>

        <p class="inv-hw-hero__eyebrow inv-fade-up inv-fade-up--1">{{ $heroEyebrow }}</p>
        <h1 class="inv-hw-hero__name inv-fade-up inv-fade-up--1">{{ $page->displayName }}</h1>

        @if(!empty($heroMessage))
            <p class="inv-hw-hero__message inv-fade-up inv-fade-up--2">{{ $heroMessage }}</p>
        @endif

        <div class="inv-hw-ticket inv-fade-up inv-fade-up--3">
            <span class="inv-hw-ticket__label">La noche del</span>
            <span class="inv-hw-ticket__day">{{ $heroDay }}</span>
            <span class="inv-hw-ticket__time">{{ $page->eventDate->format('H:i') }}</span>
        </div>
    </div>

    <div class="inv-hw-hill" aria-hidden="true">
        @include('invitations.partials.halloween.pumpkin', ['class' => 'inv-hw-hill__pumpkin inv-hw-hill__pumpkin--1'])
        @include('invitations.partials.halloween.pumpkin', ['class' => 'inv-hw-hill__pumpkin inv-hw-hill__pumpkin--2'])
        @include('invitations.partials.halloween.pumpkin', ['class' => 'inv-hw-hill__pumpkin inv-hw-hill__pumpkin--3'])
    </div>

    <a href="#contenido" class="inv-hero__scroll inv-hw-hero__scroll">
        Desliza
        <span class="inv-hero__scroll-line" aria-hidden="true"></span>
    </a>
</header>
