{{-- Guirnalda de banderines colgando de un cordel curvo; cada banderín se mece (estilos en themes/cumple.css) --}}
@php
    // El cordel es una curva cuadrática: y = 4 + 70·t·(1 − t); los banderines cuelgan sobre ella
    $buntingFlags = [];
    $buntingCount = 11;

    for ($i = 0; $i < $buntingCount; $i++) {
        $t = ($i + 0.5) / $buntingCount;
        $buntingFlags[] = [400 * $t, 4 + 70 * $t * (1 - $t)];
    }
@endphp
<svg class="inv-cumple-bunting {{ $class ?? '' }}" viewBox="0 0 400 48" preserveAspectRatio="none" aria-hidden="true" focusable="false">
    <path class="inv-cumple-bunting__string" d="M0 4 Q200 39 400 4" vector-effect="non-scaling-stroke"/>
    @foreach($buntingFlags as $index => [$x, $y])
        <polygon class="inv-cumple-bunting__flag" style="--i: {{ $index }}"
            points="{{ round($x - 13, 1) }},{{ round($y, 1) }} {{ round($x + 13, 1) }},{{ round($y, 1) }} {{ round($x, 1) }},{{ round($y + 24, 1) }}"/>
    @endforeach
</svg>
