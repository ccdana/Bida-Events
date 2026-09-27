{{--
    Fondo de «Edición especial»: recortes de revista con las letras del nombre, cada una recortada de
    una página distinta (su tipografía, su papel y su color), que caen despacio dando vueltas por
    delante de la revista, suaves. Valores deterministas; estilos en tendencias/revista.css. Lo incluye shell/themed-ambient.
--}}
@php
    $cutLetters = array_values(array_filter(mb_str_split(mb_strtoupper(preg_replace('/[^\p{L}\p{N}]/u', '', $page->displayName))), fn ($letter) => $letter !== ''));
    $cutLetters = array_slice($cutLetters ?: ['¡', 'A', '!'], 0, 10);
@endphp
<div class="rv-ambient" aria-hidden="true">
    @foreach($cutLetters as $index => $letter)
        <span @class(['rv-ambient__cut', 'rv-ambient__cut--'.($index % 4), 'is-extra' => $index >= 7])
            style="{{ sprintf('--x:%.1f%%;--d:%ds;--delay:-%.1fs;--r:%ddeg;--spin:%ddeg', fmod($index * 29.7 + 6, 90), 22 + ($index * 7) % 12, fmod($index * 5.3, 26), (($index * 37) % 50) - 25, ($index % 2 ? 1 : -1) * (120 + ($index * 23) % 140)) }}">{{ $letter }}</span>
    @endforeach
</div>
