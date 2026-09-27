{{--
    Fondo de «Partitura a dos voces»: la música sigue sonando detrás de la invitación. Dos pentagramas
    cruzan la pantalla ondulando como si vibraran y las notas (negras, corcheas y corcheas unidas)
    suben despacio meciéndose. Valores deterministas; estilos en tendencias/partitura.css. Lo incluye
    shell/themed-ambient.
--}}
<div class="pt-ambient" aria-hidden="true">
    @foreach([1, 2] as $staff)
        <svg class="pt-ambient__staff pt-ambient__staff--{{ $staff }}" viewBox="0 0 800 60" preserveAspectRatio="none" focusable="false">
            @for($line = 0; $line < 5; $line++)
                <path d="M0 {{ 10 + $line * 10 }} Q100 {{ 2 + $line * 10 }} 200 {{ 10 + $line * 10 }} T400 {{ 10 + $line * 10 }} T600 {{ 10 + $line * 10 }} T800 {{ 10 + $line * 10 }}"/>
            @endfor
        </svg>
    @endforeach
</div>

{{-- Las notas suben por delante, suaves y sin atrapar toques --}}
<div class="pt-ambient pt-ambient--front" aria-hidden="true">
    @for($i = 0; $i < 10; $i++)
        <span @class(['pt-ambient__note', 'pt-ambient__note--'.($i % 3), 'is-extra' => $i >= 7])
            style="{{ sprintf('--x:%.1f%%;--s:%.2frem;--d:%ds;--delay:-%.1fs;--sway:%dpx;--r:%ddeg', fmod($i * 33.1 + 7, 90), 1.3 + ($i % 4) * 0.35, 18 + ($i * 5) % 10, fmod($i * 3.9, 24), ($i % 2 ? 1 : -1) * (14 + ($i * 9) % 20), (($i * 29) % 30) - 15) }}"></span>
    @endfor
</div>
