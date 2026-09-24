{{--
    Fondo de la graduación: birretes que suben despacio girando y destellos dorados.
    Valores deterministas para que el HTML sea igual en cada visita (estilos en themes/graduacion.css).
--}}
<div class="inv-grad-ambient" aria-hidden="true">
    @for($i = 0; $i < 6; $i++)
        <span class="inv-grad-ambient__cap" style="{{ sprintf('--x:%.1f%%;--s:%.2f;--d:%ds;--delay:-%ds;--r:%ddeg', fmod($i * 31.7 + 6, 90), 0.5 + ($i % 3) * 0.18, 26 + ($i * 7) % 14, ($i * 6) % 30, (($i * 53) % 60) - 30) }}">
            @include('invitations.partials.graduacion.cap')
        </span>
    @endfor

    @for($i = 0; $i < 14; $i++)
        <i class="inv-grad-ambient__spark" style="{{ sprintf('--x:%.1f%%;--y:%.1f%%;--d:%.1fs;--delay:-%.1fs;--s:%dpx', fmod($i * 43.3 + 4, 100), fmod($i * 29.9 + 7, 100), 3 + ($i % 4) * 0.8, fmod($i * 1.3, 6), 6 + ($i * 5) % 8) }}"></i>
    @endfor
</div>
