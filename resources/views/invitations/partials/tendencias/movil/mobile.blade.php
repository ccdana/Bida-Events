{{--
    El móvil de la cuna: el gancho, la varilla de madera con sus cuentas y cinco figuras de fieltro
    (la luna, la estrella, la vela del bautismo, la casita y el corderito), cada una con su hilo de
    distinto largo y su propio ritmo al mecerse. Lo usan la apertura y la portada. Parámetro: class.
--}}
@php
    // Forma, color de fieltro, lugar en la varilla (%), largo del hilo y cuánto tarda en mecerse
    $mobileFigures = [
        ['moon', 'mv-felt--3', 10, 46, 5.2],
        ['star', 'mv-felt--2', 30, 78, 4.4],
        ['candle', 'mv-felt--1', 50, 34, 5.8],
        ['house', 'mv-felt--2', 70, 70, 4.9],
        ['lamb', 'mv-felt--3', 90, 50, 5.5],
    ];
@endphp
<div class="mv-mobile {{ $class ?? '' }}" aria-hidden="true">
    <span class="mv-mobile__hook"></span>
    <span class="mv-mobile__bar"></span>
    @foreach($mobileFigures as [$shape, $felt, $left, $length, $time])
        <span class="mv-hang" style="--x: {{ $left }}%; --len: {{ $length }}px; --t: {{ $time }}s; --i: {{ $loop->index }}">
            @include('invitations.partials.tendencias.movil.figure', ['shape' => $shape, 'class' => $felt])
        </span>
    @endforeach
</div>
