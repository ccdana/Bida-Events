{{--
    El panel de una temporada dentro de la hoja de temporadas (site/partials/season): arriba, de qué se
    trata y la oferta (precio normal tachado y el de promoción, la cuenta regresiva y el botón de
    WhatsApp); abajo, sus diseños como en la portada: capturas de cómo empieza cada apertura, en fila,
    que llevan a la muestra completa (site/partials/shots). Con varias temporadas, arriba va el índice
    para pasar de una a otra: el nombre de cada una y hasta cuándo dura, con un filete dorado debajo de
    la que se ve (se repite en cada panel para que tome su fondo). Recibe $season y $seasons
    (HomeController::seasons).
--}}
@php
    $seasonLeft = now()->diff($season['endsAt']);
    $seasonUnits = [
        'days' => ['value' => (int) $seasonLeft->days, 'label' => 'días'],
        'hours' => ['value' => $seasonLeft->h, 'label' => 'horas'],
        'minutes' => ['value' => $seasonLeft->i, 'label' => 'min'],
        'seconds' => ['value' => $seasonLeft->s, 'label' => 'seg'],
    ];
    $seasonUntil = $season['endsAt']->locale('es')->translatedFormat('l j \d\e F');
    $seasonDemos = $season['demos'];
    $seasonProduct = $season['product'] ?? 'tarjeta';
    $seasonId = 'temporada-'.$season['key'];
    $seasonSwitch = count($seasons ?? []) > 1;
@endphp

<div id="{{ $seasonId }}" data-season="{{ $season['key'] }}" class="site-season site-season--{{ $season['key'] }}"
    x-show="current === @js($season['key'])" @if(! $loop->first) x-cloak @endif
    x-data="seasonOffer(@js($season['endsAt']->toIso8601String()))"
    @if($seasonSwitch) role="tabpanel" @endif
    aria-labelledby="{{ $seasonId }}-titulo">
    <div @class(['site-season__panel', 'has-switch' => $seasonSwitch])>
        <span class="site-season__decor" aria-hidden="true">
            @for($piece = 0; $piece < 9; $piece++)
                <i style="--i: {{ $piece }}"></i>
            @endfor
        </span>

        <button type="button" class="site-season__close" @click="close()" aria-label="Cerrar">
            <x-phosphor-x aria-hidden="true" />
        </button>

        @if($seasonSwitch)
            <div class="site-season__switch" role="tablist" aria-label="Temporadas"
                @keydown.arrow-right.prevent="step(1)" @keydown.arrow-left.prevent="step(-1)">
                @foreach($seasons as $other)
                    @php($isCurrent = $other['key'] === $season['key'])
                    <button type="button" role="tab" @click="pick(@js($other['key']))"
                        aria-controls="temporada-{{ $other['key'] }}" aria-selected="{{ $isCurrent ? 'true' : 'false' }}"
                        tabindex="{{ $isCurrent ? 0 : -1 }}" @class(['site-season__tab', 'is-active' => $isCurrent])>
                        <span class="site-season__tab-name">{{ $other['name'] }}</span>
                        <span class="site-season__tab-until">Hasta el {{ $other['endsAt']->locale('es')->translatedFormat('j M') }}</span>
                    </button>
                @endforeach
            </div>
        @endif

        <div class="site-season__head">
            <div class="site-season__copy">
                <p class="site-season__eyebrow">{{ $seasonProduct === 'tarjeta' ? 'Tarjetas' : 'Invitaciones' }} de temporada · {{ $season['date'] ?? $seasonUntil }}</p>
                <h2 id="{{ $seasonId }}-titulo" class="site-season__title">{{ $season['title'] }}</h2>
                <p class="site-season__text">{{ $season['text'] }}</p>
            </div>

            <div class="site-season__deal">
                <p class="site-season__price">
                    <span class="site-season__label">Cada {{ $seasonProduct }}</span>
                    <span class="site-season__amount">
                        @if($season['old_price'])
                            <del><span class="sr-only">Antes </span>{{ \App\Support\Money::format($season['old_price']) }}</del>
                            <span class="sr-only">, ahora</span>
                        @endif
                        <strong>{{ $season['final_price'] }}</strong>
                        <span class="site-season__currency">{{ \App\Support\Money::code() }}</span>
                    </span>
                </p>

                <div class="site-season__countdown" role="timer" aria-label="Tiempo que queda para pedirla">
                    <span class="site-season__label">Quedan</span>
                    <span class="site-season__units">
                        @foreach($seasonUnits as $key => $unit)
                            <span class="site-season__unit">
                                <b x-text="pad(left.{{ $key }})">{{ str_pad((string) $unit['value'], 2, '0', STR_PAD_LEFT) }}</b>
                                <small>{{ $unit['label'] }}</small>
                            </span>
                        @endforeach
                    </span>
                    <span class="site-season__until">Hasta el {{ $seasonUntil }}</span>
                </div>

                <div class="site-season__actions">
                    <a href="{{ $season['whatsappUrl'] }}" target="_blank" rel="noopener" class="site-btn site-btn--lg site-season__cta">
                        <x-phosphor-whatsapp-logo aria-hidden="true" />
                        La quiero por {{ \App\Support\Money::format($season['final_price']) }}
                    </a>
                    @if($season['landingUrl'])
                        <a href="{{ $season['landingUrl'] }}" class="site-season__link">Ver todo sobre {{ $season['name'] }} <x-phosphor-arrow-right aria-hidden="true" /></a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Los diseños de la temporada, como en «Pruébala como invitado» --}}
        <div class="site-season__designs">
            <p class="site-season__label">
                Diseños de la temporada
                <span class="site-season__count">{{ count($seasonDemos) }}</span>
            </p>
            @include('site.partials.shots', ['demos' => $seasonDemos, 'noun' => $seasonProduct, 'layout' => 'row', 'label' => 'Diseños de '.$season['name']])
            @if(! empty($season['more_note']))
                <p class="site-season__more-note">{{ $season['more_note'] }}</p>
            @endif
        </div>
    </div>
</div>
