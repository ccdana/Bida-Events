{{--
    Brillos de fondo de XV: resplandores dorados que se desplazan y destellos que titilan.
    Valores deterministas para que el HTML sea igual en cada visita (estilos en themes/xv.css).
--}}
<div class="inv-xv-glints" aria-hidden="true">
    <span class="inv-xv-glints__orb inv-xv-glints__orb--1"></span>
    <span class="inv-xv-glints__orb inv-xv-glints__orb--2"></span>
    <span class="inv-xv-glints__orb inv-xv-glints__orb--3"></span>

    @for($i = 0; $i < 12; $i++)
        <span class="inv-xv-glints__star" style="{{ sprintf('--top:%.1f%%;--left:%.1f%%;--size:%dpx;--d:%.1fs;--delay:-%.1fs', fmod($i * 31.7 + 6, 100), fmod($i * 57.1 + 13, 100), 7 + ($i * 5) % 8, 2.8 + ($i % 4) * 0.8, fmod($i * 1.1, 4)) }}"></span>
    @endfor
</div>
