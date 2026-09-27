{{--
    Fondo de «Carta de baile»: el salón de la fiesta. Un brillo recorre el terciopelo cada tanto, como
    cuando alguien pasa frente a una lámpara, y las luces de la araña se reflejan en círculos cálidos
    que giran despacio de a dos, como parejas en el vals. Valores deterministas; estilos en
    themes/carta.css. Lo incluye shell/themed-ambient.
--}}
<div class="cb-ambient" aria-hidden="true">
    <span class="cb-ambient__sheen"></span>
</div>

{{-- Las luces de la araña pasan por delante: aclaran apenas lo que tocan --}}
<div class="cb-ambient cb-ambient--front" aria-hidden="true">
    @for($pair = 0; $pair < 5; $pair++)
        <span @class(['cb-ambient__pair', 'is-extra' => $pair >= 3])
            style="{{ sprintf('--x:%.1f%%;--y:%.1f%%;--s:%.2frem;--d:%ds;--delay:-%.1fs;--drift:%dpx', fmod($pair * 37.3 + 12, 84), fmod($pair * 23.9 + 14, 78), 3.2 + ($pair % 3) * 1.4, 14 + ($pair * 5) % 9, fmod($pair * 4.3, 20), ($pair % 2 ? 1 : -1) * (20 + $pair * 6)) }}">
            <i></i><i></i>
        </span>
    @endfor
</div>
