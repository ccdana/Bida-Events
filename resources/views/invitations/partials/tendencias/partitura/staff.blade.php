{{--
    Un pentagrama con su melodía, en SVG. Cinco líneas, las notas (cabeza inclinada y plica) y, si
    se pide, la barra final doble. Las notas se dibujan una tras otra con --note.
    Parámetros: notes (lista de [x, y] en un pentagrama de 300 × 60; y = 10…50, de arriba abajo),
    stem ('up' o 'down'), class, final (barra doble al final), meter (el compás 4/4 al principio).
--}}
<svg class="pt-staff {{ $class ?? '' }}" viewBox="0 0 300 60" aria-hidden="true" focusable="false">
    <g class="pt-staff__lines">
        @foreach([10, 20, 30, 40, 50] as $lineY)
            <line x1="0" y1="{{ $lineY }}" x2="300" y2="{{ $lineY }}"/>
        @endforeach
    </g>
    @if(!empty($meter))
        {{-- Compás de cuatro tiempos: el pulso tranquilo de un vals lento o de una marcha --}}
        <text class="pt-staff__meter" x="10" y="27" text-anchor="middle">4</text>
        <text class="pt-staff__meter" x="10" y="47" text-anchor="middle">4</text>
    @endif
    <g class="pt-staff__notes">
        @foreach($notes as $index => [$noteX, $noteY])
            <g class="pt-note" style="--note: {{ $index }}">
                <ellipse cx="{{ $noteX }}" cy="{{ $noteY }}" rx="5.2" ry="3.8" transform="rotate(-22 {{ $noteX }} {{ $noteY }})"/>
                @if(($stem ?? 'up') === 'up')
                    <line x1="{{ $noteX + 4.6 }}" y1="{{ $noteY - 1 }}" x2="{{ $noteX + 4.6 }}" y2="{{ $noteY - 26 }}"/>
                @else
                    <line x1="{{ $noteX - 4.6 }}" y1="{{ $noteY + 1 }}" x2="{{ $noteX - 4.6 }}" y2="{{ $noteY + 26 }}"/>
                @endif
            </g>
        @endforeach
    </g>
    @if(!empty($final))
        <g class="pt-staff__bar">
            <line x1="291" y1="10" x2="291" y2="50"/>
            <rect x="295" y="10" width="4" height="40"/>
        </g>
    @endif
</svg>
