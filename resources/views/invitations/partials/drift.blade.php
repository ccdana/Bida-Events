{{--
    Partículas de fondo reutilizables (estilos en resources/css/invitation/ambient.css, «Partículas por tipo»).
    Cada plantilla suma las suyas a las propias para tener al menos dos tipos en movimiento.

    Uso: @include('invitations.partials.drift', ['kind' => 'leaf', 'count' => 12, 'mobile' => 7, 'seed' => 2, 'class' => ''])
    - kind: leaf (hojas que caen), feather (plumas que bajan meciéndose), streamer (serpentinas que giran),
      star (destellos que suben), bokeh (luces difusas que flotan) o twinkle (estrellas quietas que titilan).
    - count: cuántas; mobile: cuántas en el celular (las demás se ocultan para cuidar la batería).
    - seed: cambia posiciones y tiempos para que dos capas del mismo tipo no se vean iguales.
    - class: modificador de color de la plantilla (por ejemplo inv-drift--otono).

    Valores deterministas: el HTML es igual en cada visita y la caché no se invalida.
--}}
@php
    $driftKind = $kind ?? 'bokeh';
    $driftCount = $count ?? 10;
    $driftMobile = $mobile ?? (int) ceil($driftCount * 0.6);
    $driftSeed = $seed ?? 1;
@endphp
<div class="inv-drift inv-drift--{{ $driftKind }} {{ $class ?? '' }}" aria-hidden="true">
    @for($i = 0; $i < $driftCount; $i++)
        @php
            $n = $i + $driftSeed * 7;
            $duration = 11 + ($n * 7) % 11 + ($driftKind === 'feather' ? 6 : 0);
            $style = sprintf(
                '--x:%.1f%%;--y:%.1f%%;--s:%.2f;--dx:%dpx;--r:%ddeg;--d:%ds;--delay:-%.1fs',
                fmod($n * 37.9 + 5, 98),
                fmod($n * 23.3 + 4, 55),
                0.7 + (($n * 5) % 7) / 10,
                (($n * 31) % 90) - 45,
                (($n * 67) % 360),
                $duration,
                fmod($n * 3.3, $duration),
            );
        @endphp
        <span @class(['is-extra' => $i >= $driftMobile]) style="{{ $style }}"></span>
    @endfor
</div>
