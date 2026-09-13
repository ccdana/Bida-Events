{{-- Portada de boda: foto en arco con ramas que crecen, nombres de los novios y fecha --}}
@php
    $heroEyebrow = ($page->welcome['subtitulo'] ?? null) ?: ($invCopy['hero_eyebrow'] ?? 'Nos casamos');
    $heroMessage = $page->welcome['mensaje'] ?? '';
    $heroDateText = ($page->welcome['fecha_texto'] ?? null)
        ?: \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('l j \d\e F, Y'));
@endphp

<header id="inicio" class="inv-hero inv-boda-hero">
    <div class="inv-boda-hero__inner">
        <p class="inv-boda-hero__eyebrow inv-fade-up">{{ $heroEyebrow }}</p>

        <div class="inv-boda-arch inv-fade-up inv-fade-up--1">
            <div class="inv-boda-arch__window">
                @if($page->heroImage)
                    @php($heroSrcset = \App\Support\CloudinaryImage::srcset($page->heroImage, [480, 768, 1200]))
                    <img
                        src="{{ \App\Support\CloudinaryImage::url($page->heroImage, 1200) }}"
                        @if($heroSrcset) srcset="{{ $heroSrcset }}" sizes="(min-width: 640px) 17rem, 60vw" @endif
                        alt=""
                        class="inv-boda-arch__photo"
                        loading="eager"
                        fetchpriority="high"
                        decoding="async"
                    >
                @else
                    <span class="inv-boda-arch__monogram">{{ $page->initials() }}</span>
                @endif
            </div>
            @include('invitations.partials.boda.branch', ['class' => 'inv-boda-arch__branch inv-boda-arch__branch--left'])
            @include('invitations.partials.boda.branch', ['class' => 'inv-boda-arch__branch inv-boda-arch__branch--right'])
        </div>

        <h1 class="inv-boda-hero__names inv-fade-up inv-fade-up--2">
            @foreach($page->names() as $index => $name)
                @if($index > 0)
                    <span class="inv-boda-hero__amp">&amp;</span>
                @endif
                <span>{{ $name }}</span>
            @endforeach
        </h1>

        @if(!empty($heroMessage))
            <p class="inv-boda-hero__message inv-fade-up inv-fade-up--2">{{ $heroMessage }}</p>
        @endif

        <p class="inv-boda-hero__date inv-fade-up inv-fade-up--3" aria-hidden="true">
            <span>{{ $page->eventDate->format('d') }}</span><i></i><span>{{ $page->eventDate->format('m') }}</span><i></i><span>{{ $page->eventDate->format('Y') }}</span>
        </p>
        <p class="inv-boda-hero__date-text inv-fade-up inv-fade-up--3">{{ $heroDateText }}</p>

        <a href="#contenido" class="inv-hero__scroll inv-boda-hero__scroll">
            Desliza
            <span class="inv-hero__scroll-line" aria-hidden="true"></span>
        </a>
    </div>
</header>
