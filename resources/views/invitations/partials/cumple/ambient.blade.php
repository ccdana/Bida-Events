{{--
    Fondo vivo del cumpleaños: globos que suben despacio y confeti que cae girando.
    Valores deterministas para que el HTML sea igual en cada visita (estilos en themes/cumple.css).
--}}
<div class="inv-cumple-ambient" aria-hidden="true">
    @for($i = 0; $i < 8; $i++)
        <span class="inv-cumple-ambient__balloon" style="{{ sprintf('--x:%.1f%%;--s:%.2f;--d:%ds;--delay:-%ds;--sway:%dpx', fmod($i * 29.3 + 8, 92), 0.6 + ($i % 3) * 0.2, 22 + ($i * 7) % 12, ($i * 5) % 30, 10 + ($i * 7) % 16) }}">
            @include('invitations.partials.cumple.balloon')
        </span>
    @endfor

    @for($i = 0; $i < 16; $i++)
        <i class="inv-cumple-ambient__confetti" style="{{ sprintf('--x:%.1f%%;--d:%ds;--delay:-%.1fs;--dx:%dpx;--r:%ddeg', fmod($i * 47.9 + 3, 100), 9 + ($i * 3) % 7, fmod($i * 1.7, 12), (($i * 31) % 80) - 40, 360 + ($i * 90) % 720) }}"></i>
    @endfor
</div>
