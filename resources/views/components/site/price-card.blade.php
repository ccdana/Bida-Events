{{--
    Una columna de precios del sitio: nombre con su etiqueta, precio (con el normal tachado si hay
    rebaja), lo que incluye (lista corta, siempre a la vista) y el botón. La usan los niveles de «Hazlo tú»
    y los planes mensuales, así las dos formas de pago se ven igual. Filas separadas por filetes, sin
    cajas. Estilos: site.css («Precios»).

    - price / old / suffix: el precio de hoy, el normal tachado (o null) y lo que va después («USD»).
    - featured: el recomendado (filete dorado arriba); premium: el de más arriba (corona y botón dorado).
    - tag: la etiqueta junto al nombre («Recomendado»).
    - cta: el texto del botón; false deja la fila sin botón (los niveles de «Hazlo tú» no se eligen: los dice el editor).
    - reserve: guarda el lugar del precio tachado y del ahorro, para que columnas con y sin rebaja alineen.
    - cycles (opcional, planes): por meses (1, 3, 12) el precio por mes, el tachado, el ahorro y el enlace;
      siguen a «cycle» del selector de la página (site.partials.diy-plans). Sin JavaScript queda el mensual.
--}}
@props([
    'name',
    'price',
    'old' => null,
    'suffix' => 'USD',
    'saving' => null,
    'summary' => '',
    'features' => [],
    'featured' => false,
    'premium' => false,
    'tag' => null,
    'url',
    'external' => false,
    'cta' => 'Elegir',
    'index' => 0,
    'reserve' => false,
    'cycles' => null,
])

<article @class(['site-plan', 'site-plan--featured' => $featured, 'site-plan--premium' => $premium]) data-reveal style="--reveal-index: {{ $index }}"
    @if($cycles) x-data="{ cycles: @js($cycles) }" @endif>
    <div class="site-plan__head">
        <h3 class="site-plan__name">
            {{ $name }}
            @if($premium)
                <x-phosphor-crown-simple-fill class="site-plan__crown" aria-hidden="true" />
            @endif
        </h3>
        @if($tag)
            <span class="site-plan__tag">{{ $tag }}</span>
        @endif
    </div>

    <p class="site-plan__amount">
        @if($cycles)
            <del @class(['site-plan__old', 'invisible' => ! $old]) :class="{ invisible: ! cycles[cycle].old }" :aria-hidden="(! cycles[cycle].old).toString()">
                <span class="sr-only">{{ __('Antes') }} </span><span x-text="cycles[cycle].old || String.fromCharCode(160)">{!! $old ? e($old) : '&nbsp;' !!}</span>
            </del>
        @elseif($old)
            <del class="site-plan__old"><span class="sr-only">{{ __('Antes') }} </span>{{ $old }}</del>
            <span class="sr-only">{{ __(', ahora') }}</span>
        @elseif($reserve)
            <span class="site-plan__old invisible" aria-hidden="true">&nbsp;</span>
        @endif
        <span class="flex items-baseline gap-2">
            <span class="site-plan__price" @if($cycles) x-text="cycles[cycle].price" @endif>{{ $price }}</span>
            <span class="text-xl text-site-muted">{{ $suffix }}</span>
        </span>
    </p>
    @if($cycles)
        <p @class(['site-plan__saving', 'invisible' => ! $saving]) :class="{ invisible: ! cycles[cycle].saving }" x-text="cycles[cycle].saving || String.fromCharCode(160)">{!! $saving ? e($saving) : '&nbsp;' !!}</p>
    @elseif($saving)
        <p class="site-plan__saving">{{ $saving }}</p>
    @elseif($reserve)
        <p class="site-plan__saving invisible" aria-hidden="true">&nbsp;</p>
    @endif

    <p class="site-plan__summary">{{ $summary }}</p>

    <ul class="site-plan__features">
        @foreach($features as $feature)
            <li>
                <x-phosphor-check-bold class="site-plan__check" aria-hidden="true" />
                <span>{{ $feature }}</span>
            </li>
        @endforeach
    </ul>

    @if($cta)
        <a href="{{ $url }}" @if($cycles) :href="cycles[cycle].url" @endif @if($external) target="_blank" rel="noopener" @endif
            @class([
                'site-btn site-btn--lg site-plan__cta justify-center',
                'site-btn--gold' => $premium,
                'site-btn--ghost' => ! $featured && ! $premium,
            ])>
            {{ $cta }}
            <x-phosphor-arrow-right class="site-btn__arrow" aria-hidden="true" />
        </a>
    @endif
</article>
