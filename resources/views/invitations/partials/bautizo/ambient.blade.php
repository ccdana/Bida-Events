{{--
    Fondo vivo del bautizo: nubes que cruzan despacio y destellos que titilan en su lugar.
    Valores deterministas para que el HTML sea igual en cada visita (estilos en themes/bautizo.css).
--}}
<div class="inv-bautizo-ambient" aria-hidden="true">
    <span class="inv-bautizo-ambient__cloud inv-bautizo-ambient__cloud--1"></span>
    <span class="inv-bautizo-ambient__cloud inv-bautizo-ambient__cloud--2"></span>
    <span class="inv-bautizo-ambient__cloud inv-bautizo-ambient__cloud--3"></span>

    @for($i = 0; $i < 14; $i++)
        @php
            $style = sprintf(
                '--top:%.1f%%;--left:%.1f%%;--size:%dpx;--d:%.1fs;--delay:-%.1fs',
                fmod($i * 37.7 + 9, 100),
                fmod($i * 53.3 + 17, 100),
                9 + ($i * 5) % 10,
                2.6 + ($i % 4) * 0.7,
                fmod($i * 1.3, 4),
            );
        @endphp
        <span class="inv-bautizo-ambient__sparkle" style="{{ $style }}"></span>
    @endfor
</div>
