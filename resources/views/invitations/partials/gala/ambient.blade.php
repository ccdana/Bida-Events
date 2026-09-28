{{--
    Fondo de «Noche de gala»: la luz de la araña quebrada por los cristales. Destellos con los colores
    del arcoíris que titilan y bajan despacio, como la luz de los caireles sobre las paredes del salón.
    Valores deterministas; estilos en themes/gala.css. Lo incluye shell/themed-ambient.
--}}
<div class="ga-ambient" aria-hidden="true">
    @for($i = 0; $i < 12; $i++)
        <span @class(['ga-ambient__glint', 'is-extra' => $i >= 8])
            style="{{ sprintf('--x:%.1f%%;--s:%.2frem;--d:%ds;--delay:-%.1fs;--hue:%ddeg', fmod($i * 31.9 + 6, 94), 0.5 + ($i % 4) * 0.25, 16 + ($i * 7) % 10, fmod($i * 3.7, 22), ($i * 47) % 360) }}"></span>
    @endfor
</div>
