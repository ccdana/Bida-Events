{{--
    Fondo vivo de la plantilla de boda: manchas de color que respiran y pétalos que caen.
    Valores deterministas para que el HTML sea igual en cada visita (estilos en themes/boda.css).
--}}
<div class="inv-boda-ambient" aria-hidden="true">
    <span class="inv-boda-ambient__bloom inv-boda-ambient__bloom--1"></span>
    <span class="inv-boda-ambient__bloom inv-boda-ambient__bloom--2"></span>
    <span class="inv-boda-ambient__bloom inv-boda-ambient__bloom--3"></span>

    <div class="inv-boda-petals">
        @for($i = 0; $i < 18; $i++)
            @php
                $duration = 14 + ($i * 5) % 11;
                $style = sprintf(
                    '--x:%.1f%%;--s:%.2f;--o:%.2f;--dx:%dpx;--d:%ds;--delay:-%.1fs',
                    fmod($i * 41.3 + 7, 100),
                    0.7 + (($i * 7) % 6) / 10,
                    0.45 + (($i * 3) % 5) / 10,
                    (($i * 37) % 90) - 45,
                    $duration,
                    fmod($i * 2.9, $duration),
                );
            @endphp
            <span style="{{ $style }}"></span>
        @endfor
    </div>
</div>
