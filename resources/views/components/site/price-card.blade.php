{{--
    Una columna de precios del sitio: nombre con su etiqueta, precio (con el normal tachado si hay
    rebaja), lo que incluye y el botón. La usan los paquetes («Te la hacemos») y los planes mensuales
    de «Únete», así las dos formas de pago se ven igual. Estilos: site.css («Precios»).

    - price / old / suffix: el precio de hoy, el normal tachado (o null) y lo que va después («USD»).
    - featured: el recomendado (filete dorado arriba); premium: el de más arriba (corona y botón dorado).
    - tag: la etiqueta junto al nombre («Recomendado»).
    - reserve: guarda el lugar del precio tachado y del ahorro, para que columnas con y sin rebaja alineen.
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
])

<article @class(['site-plan', 'site-plan--featured' => $featured, 'site-plan--premium' => $premium]) data-reveal style="--reveal-index: {{ $index }}">
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
        @if($old)
            <del class="site-plan__old"><span class="sr-only">Antes </span>{{ $old }}</del>
            <span class="sr-only">, ahora</span>
        @elseif($reserve)
            <span class="site-plan__old invisible" aria-hidden="true">&nbsp;</span>
        @endif
        <span class="flex items-baseline gap-2">
            <span class="site-plan__price">{{ $price }}</span>
            <span class="text-xl text-site-muted">{{ $suffix }}</span>
        </span>
    </p>
    @if($saving)
        <p class="site-plan__saving">{{ $saving }}</p>
    @elseif($reserve)
        <p class="site-plan__saving invisible" aria-hidden="true">&nbsp;</p>
    @endif

    <p class="mt-4 max-w-[36ch] leading-relaxed text-site-muted">{{ $summary }}</p>

    <ul class="site-plan__features">
        @foreach($features as $feature)
            <li>
                <x-phosphor-check-bold class="site-plan__check" aria-hidden="true" />
                <span>{{ $feature }}</span>
            </li>
        @endforeach
    </ul>

    <a href="{{ $url }}" @if($external) target="_blank" rel="noopener" @endif
        @class([
            'site-btn site-btn--lg mt-10 justify-center',
            'site-btn--gold' => $premium,
            'site-btn--ghost' => ! $featured && ! $premium,
        ])>
        {{ $cta }}
        <x-phosphor-arrow-right class="site-btn__arrow" aria-hidden="true" />
    </a>
</article>
