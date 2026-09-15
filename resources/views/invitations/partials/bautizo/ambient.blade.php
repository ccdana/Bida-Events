{{--
    Fondo vivo del bautizo: nubes que cruzan, palomas lejanas que atraviesan el cielo, burbujas de agua
    que suben y destellos que titilan (algunos flotan). Valores deterministas para que el HTML sea igual
    en cada visita (estilos en themes/bautizo.css).
--}}
<div class="inv-bautizo-ambient" aria-hidden="true">
    @for($i = 1; $i <= 4; $i++)
        <span class="inv-bautizo-ambient__cloud inv-bautizo-ambient__cloud--{{ $i }}"></span>
    @endfor

    @include('invitations.partials.bautizo.dove', ['class' => 'inv-bautizo-ambient__dove inv-bautizo-ambient__dove--1'])
    @include('invitations.partials.bautizo.dove', ['class' => 'inv-bautizo-ambient__dove inv-bautizo-ambient__dove--2'])

    <div class="inv-bautizo-ambient__bubbles">
        @for($i = 0; $i < 12; $i++)
            @php
                $style = sprintf(
                    '--left:%.1f%%;--size:%dpx;--d:%.1fs;--delay:-%.1fs;--sway:%dpx',
                    fmod($i * 41.3 + 6, 96),
                    10 + ($i * 7) % 18,
                    13 + ($i % 5) * 2.4,
                    fmod($i * 3.7, 16),
                    ($i % 2 ? 1 : -1) * (14 + ($i * 9) % 22),
                );
            @endphp
            <span class="inv-bautizo-ambient__bubble" style="{{ $style }}"></span>
        @endfor
    </div>

    <div class="inv-bautizo-ambient__sparkles">
        @for($i = 0; $i < 24; $i++)
            @php
                $style = sprintf(
                    '--top:%.1f%%;--left:%.1f%%;--size:%dpx;--d:%.1fs;--delay:-%.1fs',
                    fmod($i * 37.7 + 9, 100),
                    fmod($i * 53.3 + 17, 100),
                    8 + ($i * 5) % 11,
                    2.2 + ($i % 4) * 0.6,
                    fmod($i * 1.3, 4),
                );
            @endphp
            <span class="inv-bautizo-ambient__sparkle" style="{{ $style }}"></span>
        @endfor
    </div>
</div>
