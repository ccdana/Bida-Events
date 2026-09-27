{{--
    Fondo de «Entre nubes»: nubes que cruzan el cielo detrás de la invitación, cada una a su altura, su
    tamaño y su velocidad (las lejanas, más chicas, lentas y pálidas). Valores deterministas; estilos
    en themes/nubes.css. Lo incluye shell/themed-ambient.
--}}
<div class="nb-ambient" aria-hidden="true">
    @foreach([[8, 9, 70, -10], [24, 5.5, 95, -52], [41, 12, 60, -30], [63, 7, 85, -70], [80, 10.5, 66, -18], [52, 4.5, 110, -88]] as $index => [$top, $size, $duration, $delay])
        <span @class(['nb-ambient__cloud', 'is-extra' => $index >= 4])
            style="--top: {{ $top }}%; --size: {{ $size }}rem; --d: {{ $duration }}s; --delay: {{ $delay }}s">
            @include('invitations.partials.nubes.cloud', ['shade' => $size > 8])
        </span>
    @endforeach
</div>
