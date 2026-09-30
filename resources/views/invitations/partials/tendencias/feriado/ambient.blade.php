{{--
    Fondo de «Día feriado»: hojas arrancadas del almanaque que bajan despacio dando vueltas, dos por
    cada costado y en espejo, con su franja y su número (solo transform y opacidad). Los papelitos los
    pone el parcial de partículas. Estilos en tendencias/feriado.css. Lo incluye shell/themed-ambient.
--}}
@php
    $ambientDay = (int) $page->eventDate->format('j');
    $ambientLeaves = [
        ['left', max(1, $ambientDay - 2), '0s'],
        ['right', max(1, $ambientDay - 1), '-7s'],
        ['left', max(1, $ambientDay - 4), '-12s'],
        ['right', max(1, $ambientDay - 3), '-19s'],
    ];
@endphp
<div class="fd-ambient" aria-hidden="true">
    @foreach($ambientLeaves as [$side, $number, $delay])
        <span class="fd-ambient__leaf fd-ambient__leaf--{{ $side }}" style="--delay: {{ $delay }}; --lane: {{ $loop->index < 2 ? 0 : 1 }}">
            <i>{{ $number }}</i>
        </span>
    @endforeach
</div>
