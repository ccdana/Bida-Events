{{--
    Los diseños como capturas de cómo empieza su apertura (bida:capturas-muestras): cada teléfono lleva
    a la muestra completa. Lo usan la página por evento y «Pruébala como invitado» de la portada.

    Parámetros: demos (ShowcaseDemos::find), noun (invitación o tarjeta), layout (fan: en abanico sobre
    una foto, hasta tres; row: en fila), eager (la primera captura se carga de entrada) y label (nombre
    de la lista para lectores de pantalla). Sin captura todavía, se ve la apertura en vivo, quieta.
--}}
@php
    $shotsLayout = ($layout ?? 'fan') === 'row' || count($demos) > 3 ? 'row' : 'fan';
@endphp
<ul class="site-shots site-shots--{{ $shotsLayout }} site-shots--{{ min(count($demos), 3) }}" aria-label="{{ $label ?? __('Diseños') }}">
    @foreach($demos as $index => $demo)
        <li class="site-shot" style="--i: {{ $index }}">
            <a href="{{ $demo['demoUrl'] }}" class="site-shot__link">
                <span class="site-phone site-shot__phone">
                    @if($demo['isNew'])
                        <span class="site-badge-new site-shot__badge">{{ __('Nuevo') }}</span>
                    @endif
                    {{-- Esqueleto con brillo hasta que llega la captura (o la apertura en vivo); entonces se funde (skeleton.css) --}}
                    <span class="site-phone__screen site-skel--fade">
                        @if($demo['capture'])
                            <img src="{{ $demo['capture'] }}" width="390" height="844" alt="" decoding="async" @if($index > 0 || empty($eager)) loading="lazy" @endif
                                onload="this.parentNode.classList.add('is-live')" onerror="this.parentNode.classList.add('is-live')">
                        @else
                            <iframe src="{{ $demo['demoUrl'] }}" title="{{ __('Apertura de :label', ['label' => $demo['label']]) }}" tabindex="-1" aria-hidden="true" loading="lazy"
                                onload="this.parentNode.classList.add('is-live')"></iframe>
                        @endif
                    </span>
                </span>
                <span class="site-shot__caption">
                    <span class="site-shot__name">{{ $demo['label'] }}</span>
                    <span class="site-shot__tagline">{{ $demo['tagline'] ?? $demo['event'] }}</span>
                    <span class="site-shot__cta">{{ __('Verla completa') }} <x-phosphor-arrow-right aria-hidden="true" /></span>
                </span>
            </a>
        </li>
    @endforeach
</ul>
