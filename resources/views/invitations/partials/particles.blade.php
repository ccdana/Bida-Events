{{--
    Partículas de fondo en movimiento (estilos en resources/css/invitation/ambient.css).
    Posiciones y tiempos deterministas: el HTML es igual en cada visita y la caché no se invalida.
--}}
<div class="inv-particles" aria-hidden="true">
    @for($i = 0; $i < 26; $i++)
        @php
            $duration = 13 + ($i * 7) % 12;
            $style = sprintf(
                '--x:%.1f%%;--s:%.2f;--o:%.2f;--dx:%dpx;--d:%ds;--delay:-%.1fs',
                fmod($i * 38.2 + 11, 100),
                0.65 + (($i * 5) % 7) / 10,
                0.45 + (($i * 3) % 5) / 10,
                (($i * 29) % 60) - 30,
                $duration,
                fmod($i * 3.7, $duration),
            );
        @endphp
        <span style="{{ $style }}"></span>
    @endfor
</div>
