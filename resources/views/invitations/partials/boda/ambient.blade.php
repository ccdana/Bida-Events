{{--
    Fondo vivo de la plantilla de boda: manchas de color que respiran, pétalos que caen y mariposas que cruzan.
    Valores deterministas para que el HTML sea igual en cada visita (estilos en themes/boda.css).
--}}
<div class="inv-boda-ambient" aria-hidden="true">
    <span class="inv-boda-ambient__bloom inv-boda-ambient__bloom--1"></span>
    <span class="inv-boda-ambient__bloom inv-boda-ambient__bloom--2"></span>
    <span class="inv-boda-ambient__bloom inv-boda-ambient__bloom--3"></span>

    <div class="inv-boda-petals">
        @for($i = 0; $i < 18; $i++)
            @php
                $duration = 12 + ($i * 5) % 10;
                $style = sprintf(
                    '--x:%.1f%%;--s:%.2f;--o:%.2f;--dx:%dpx;--d:%ds;--delay:-%.1fs',
                    fmod($i * 41.3 + 7, 100),
                    0.7 + (($i * 7) % 6) / 10,
                    0.55 + (($i * 3) % 5) / 10,
                    (($i * 37) % 90) - 45,
                    $duration,
                    fmod($i * 2.9, $duration),
                );
            @endphp
            <span style="{{ $style }}"></span>
        @endfor
    </div>

    @foreach([[18, 0.9, 26, 0], [52, 0.7, 34, 13], [78, 1, 30, 22]] as [$top, $size, $duration, $delay])
        <span class="inv-boda-butterfly" style="--y: {{ $top }}vh; --s: {{ $size }}; --d: {{ $duration }}s; --delay: -{{ $delay }}s">
            <svg viewBox="0 0 40 32" focusable="false">
                <path class="inv-boda-butterfly__wing inv-boda-butterfly__wing--left" d="M20 16 C14 2 2 2 3 10 C4 16 12 18 20 16 Z M20 17 C12 18 6 26 10 29 C14 31 19 24 20 17 Z"/>
                <path class="inv-boda-butterfly__wing inv-boda-butterfly__wing--right" d="M20 16 C26 2 38 2 37 10 C36 16 28 18 20 16 Z M20 17 C28 18 34 26 30 29 C26 31 21 24 20 17 Z"/>
                <path class="inv-boda-butterfly__body" d="M20 9 L20 25"/>
            </svg>
        </span>
    @endforeach
</div>
