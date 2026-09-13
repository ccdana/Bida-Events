{{--
    Franja de nubes al pie de la portada: dos capas con el mismo patrón repetido dos veces,
    así pueden desplazarse sin cortes. La capa delantera usa el fondo de la invitación.
--}}
@php
    $cloudRow = function (array $bumps, int $base): string {
        $path = "M0 120 L0 {$base}";
        $x = 0;

        for ($repeat = 0; $repeat < 2; $repeat++) {
            foreach ($bumps as [$width, $top]) {
                $next = $x + $width;
                $path .= " C{$x} {$top} {$next} {$top} {$next} {$base}";
                $x = $next;
            }
        }

        return $path." L{$x} 120 Z";
    };
    // Cada patrón suma 720 de ancho
    $backRow = $cloudRow([[110, 10], [90, 30], [130, 0], [100, 24], [120, 6], [170, 18]], 70);
    $frontRow = $cloudRow([[140, 34], [100, 48], [160, 26], [120, 44], [200, 30]], 88);
@endphp

<div class="inv-bautizo-clouds" aria-hidden="true">
    <svg class="inv-bautizo-clouds__layer inv-bautizo-clouds__layer--back" viewBox="0 0 1440 120" preserveAspectRatio="none" focusable="false">
        <path d="{{ $backRow }}"/>
    </svg>
    <svg class="inv-bautizo-clouds__layer inv-bautizo-clouds__layer--front" viewBox="0 0 1440 120" preserveAspectRatio="none" focusable="false">
        <path d="{{ $frontRow }}"/>
    </svg>
</div>
