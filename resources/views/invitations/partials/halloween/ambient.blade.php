{{--
    Fondo de la noche: niebla que se mueve abajo, chispas de vela que suben y algún murciélago que
    cruza. Valores deterministas para que el HTML sea igual en cada visita (themes/halloween.css).
--}}
<div class="inv-hw-ambient" aria-hidden="true">
    <span class="inv-hw-ambient__fog inv-hw-ambient__fog--back"></span>
    <span class="inv-hw-ambient__fog inv-hw-ambient__fog--front"></span>

    @for($i = 0; $i < 3; $i++)
        <span class="inv-hw-ambient__bat" style="{{ sprintf('--y:%d%%;--d:%ds;--delay:-%ds;--s:%.2f', 12 + $i * 23, 18 + $i * 6, $i * 7, 0.6 + ($i % 2) * 0.3) }}">
            @include('invitations.partials.halloween.bat')
        </span>
    @endfor

    @for($i = 0; $i < 14; $i++)
        <i class="inv-hw-ambient__ember" style="{{ sprintf('--x:%.1f%%;--d:%ds;--delay:-%.1fs;--dx:%dpx;--s:%dpx', fmod($i * 37.3 + 5, 100), 9 + ($i * 3) % 8, fmod($i * 1.9, 14), (($i * 29) % 60) - 30, 3 + $i % 3) }}"></i>
    @endfor
</div>
