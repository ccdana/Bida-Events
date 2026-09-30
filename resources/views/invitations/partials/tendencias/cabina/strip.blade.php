{{--
    La tira de la cabina de fotos (la usan la apertura y la portada): tres poses de la misma foto, cada
    una con su encuadre y su revelado (a color, en blanco y negro, en tono cálido con el sticker de la
    edad) y el sello naranja de la fecha en la esquina; al pie, lo que imprime la cabina de la fiesta:
    el nombre, el día, la hora y el lugar. Parámetros: class (opcional), heading (true: el nombre del
    pie es texto común; la portada lleva su h1 aparte). Necesita $page e $invCopy.
--}}
@php
    $stripDate = $page->eventDate->locale('es');
    $stripStamp = $stripDate->format('d m')." '".$stripDate->format('y');
    $stripAge = $page->age();
    $stripDay = \Illuminate\Support\Str::ucfirst($stripDate->translatedFormat('l j \d\e F'));
@endphp

<div class="cb-strip {{ $class ?? '' }}">
    @foreach([1, 2, 3] as $pose)
        <figure class="cb-frame cb-frame--{{ $pose }}" @if($pose > 1) aria-hidden="true" @endif>
            @include('invitations.partials.tendencias.photo', ['widths' => [480, 768], 'width' => 768, 'sizes' => '(min-width: 640px) 16rem, 62vw'])
            @if($pose === 3 && $stripAge !== null)
                <span class="cb-sticker" aria-hidden="true"><b>{{ $stripAge }}</b>{{ $invCopy['booth_age'] ?? 'años' }}</span>
            @endif
            <span class="cb-stamp" aria-hidden="true">{{ $stripStamp }}</span>
        </figure>
    @endforeach

    <div class="cb-strip__foot">
        <p class="cb-strip__name">{{ $page->displayName }}@if($stripAge !== null) <span>· {{ $stripAge }}</span>@endif</p>
        <p class="cb-strip__date">{{ $stripDay }}</p>
        <p class="cb-strip__meta">
            {{ $stripDate->format('H:i') }}
            @if($page->placeName)
                · {{ $page->placeName }}
            @endif
        </p>
    </div>
</div>
