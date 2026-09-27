{{--
    Fondo de «Tendedero»: el patio sigue detrás de la invitación. Dos tendederos lejanos cruzan la
    pantalla con ropita chiquita que el viento mece y corre despacio, y burbujas de jabón con reflejos
    de colores suben meciéndose y se revientan arriba. Valores deterministas; estilos en
    tendencias/tendedero.css. Lo incluye shell/themed-ambient.
--}}
<div class="td-ambient" aria-hidden="true">
    <span class="td-ambient__line td-ambient__line--1"><i></i></span>
    <span class="td-ambient__line td-ambient__line--2"><i></i></span>
</div>

{{-- Las burbujas flotan por delante, sin atrapar toques --}}
<div class="td-ambient td-ambient--front" aria-hidden="true">
    @for($i = 0; $i < 9; $i++)
        <span @class(['td-ambient__bubble', 'is-extra' => $i >= 6])
            style="{{ sprintf('--x:%.1f%%;--s:%.2frem;--d:%ds;--delay:-%.1fs;--sway:%dpx', fmod($i * 37.3 + 8, 92), 0.9 + ($i % 4) * 0.45, 16 + ($i * 5) % 9, fmod($i * 4.1, 18), ($i % 2 ? 1 : -1) * (12 + ($i * 7) % 18)) }}"></span>
    @endfor
</div>
