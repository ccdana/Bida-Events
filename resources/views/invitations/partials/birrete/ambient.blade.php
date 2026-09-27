{{--
    Fondo de «Birrete al aire»: después del lanzamiento, los birretes siguen en el aire. Suben despacio
    dando tumbos, cada uno con su borla meciéndose, y vuelven a aparecer abajo. Valores deterministas;
    estilos en themes/birrete.css. Lo incluye shell/themed-ambient.
--}}
<div class="br-ambient" aria-hidden="true">
    @for($i = 0; $i < 7; $i++)
        <span @class(['br-ambient__cap', 'is-extra' => $i >= 5])
            style="{{ sprintf('--x:%.1f%%;--s:%.2frem;--d:%ds;--delay:-%.1fs;--spin:%ddeg;--sway:%dpx', fmod($i * 31.7 + 5, 90), 2.2 + ($i % 3) * 0.9, 20 + ($i * 7) % 12, fmod($i * 4.7, 26), ($i % 2 ? 1 : -1) * (160 + ($i * 43) % 200), ($i % 2 ? -1 : 1) * (18 + ($i * 11) % 24)) }}">
            @include('invitations.partials.birrete.cap', ['side' => $i % 2 ? 'left' : 'right'])
        </span>
    @endfor
</div>
