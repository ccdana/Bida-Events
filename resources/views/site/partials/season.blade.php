{{--
    Temporada en la portada: tarjetas que se venden solo por unos días (hoy, Día del Amor y la
    Primavera). Va arriba de todo como gancho y habla de la temporada, no de un diseño: los diseños se
    listan y se van sumando en config('bida.season.templates'). Precio normal tachado, el de promoción,
    cuenta regresiva hasta config('bida.season.ends_at') (BIDA_SEASON_ENDS_AT) y un teléfono donde el
    diseño elegido se abre solo, sin pantallazos en blanco (data-cover-reel, resources/js/site.js).
    Recibe $season (HomeController::season). Al terminar la cuenta la sección se oculta sola, y
    pasada la fecha el servidor ya no la manda. Estilos: resources/css/site/site.css («Temporada»).
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
    $seasonReel = array_map(fn (array $demo) => ['url' => $demo['coverUrl'], 'label' => $demo['label']], $seasonDemos);
@endphp

<section id="temporada" class="site-season scroll-mt-20" aria-labelledby="temporada-titulo"
    x-data="seasonOffer(@js($season['endsAt']->toIso8601String()), @js(array_column($seasonDemos, 'demoUrl')))"
    x-show="!expired">
    <div class="mx-auto max-w-7xl px-4 pt-3 sm:px-5 lg:px-8 lg:pt-6">
        <div class="site-season__panel site-enter">
            <span class="site-season__petals" aria-hidden="true">
                @for($petal = 0; $petal < 9; $petal++)
                    <i style="--i: {{ $petal }}"></i>
                @endfor
            </span>

            <div class="site-season__copy">
                <p class="site-season__eyebrow">Tarjetas de temporada · {{ $season['date'] ?? $seasonUntil }}</p>
                <h2 id="temporada-titulo" class="site-season__title">{{ $season['title'] }}</h2>
                <p class="site-season__text">{{ $season['text'] }}</p>

                <div class="site-season__designs">
                    <p class="site-season__label">Diseños de la temporada</p>
                    <ol role="list">
                        @foreach($seasonDemos as $index => $demo)
                            <li>
                                <button type="button" @class(['site-season__design', 'is-active' => $index === 0])
                                    :class="{ 'is-active': active === {{ $index }} }"
                                    aria-pressed="{{ $index === 0 ? 'true' : 'false' }}"
                                    :aria-pressed="(active === {{ $index }}).toString()"
                                    @click="choose({{ $index }})">
                                    <span class="site-season__design-num">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                                    <span class="site-season__design-name">{{ $demo['label'] }}</span>
                                    @if($demo['tagline'])
                                        <span class="site-season__design-line">{{ $demo['tagline'] }}</span>
                                    @endif
                                </button>
                            </li>
                        @endforeach
                    </ol>
                    @if(! empty($season['more_note']))
                        <p class="site-season__more-note">{{ $season['more_note'] }}</p>
                    @endif
                </div>

                <div class="site-season__offer">
                    <p class="site-season__price">
                        <span class="site-season__label">Cada tarjeta</span>
                        <span class="site-season__amount">
                            @if($season['old_price'])
                                <del><span class="sr-only">Antes </span>{{ $season['old_price'] }} Bs</del>
                                <span class="sr-only">, ahora</span>
                            @endif
                            <strong>{{ $season['final_price'] }}</strong>
                            <span class="site-season__currency">Bs</span>
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
                </div>

                <div class="site-season__actions">
                    <a href="{{ $season['whatsappUrl'] }}" target="_blank" rel="noopener" class="site-btn site-btn--lg site-season__cta" data-magnetic>
                        <x-phosphor-whatsapp-logo aria-hidden="true" />
                        La quiero por {{ $season['final_price'] }} Bs
                    </a>
                    <a href="{{ $seasonDemos[0]['demoUrl'] }}" :href="demos[active]" target="_blank" rel="noopener" class="site-season__link">
                        Abrirla en pantalla completa
                        <x-phosphor-arrow-up-right aria-hidden="true" />
                    </a>
                    @if($season['landingUrl'])
                        <a href="{{ $season['landingUrl'] }}" class="site-season__link">Ver las tarjetas de temporada</a>
                    @endif
                </div>
            </div>

            <div class="site-season__stage">
                <div class="site-phone site-phone--season" x-ref="reel"
                    data-cover-reel="{{ json_encode($seasonReel, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}"
                    data-cover-reel-replay data-cover-reel-dwell="15000">
                    <div class="site-phone__screen">
                        <iframe src="{{ $seasonDemos[0]['coverUrl'] }}" tabindex="-1" aria-hidden="true" data-cover-reel-frame
                            title="Muestra de las tarjetas de temporada"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    /* Cuenta regresiva de la temporada; el teléfono cambia de diseño con «reel:go» (resources/js/site.js) */
    function seasonOffer(endsAt, demos) {
        const end = new Date(endsAt).getTime();

        return {
            demos,
            active: 0,
            expired: false,
            left: { days: 0, hours: 0, minutes: 0, seconds: 0 },
            timer: null,

            init() {
                this.tick();
                this.timer = setInterval(() => this.tick(), 1000);
            },

            tick() {
                const ms = Math.max(end - Date.now(), 0);

                if (ms === 0) {
                    this.expired = true;
                    clearInterval(this.timer);
                    return;
                }

                const seconds = Math.floor(ms / 1000);
                this.left = {
                    days: Math.floor(seconds / 86400),
                    hours: Math.floor(seconds % 86400 / 3600),
                    minutes: Math.floor(seconds % 3600 / 60),
                    seconds: seconds % 60,
                };
            },

            choose(index) {
                this.active = index;
                this.$refs.reel.dispatchEvent(new CustomEvent('reel:go', { detail: index }));
            },

            pad(value) {
                return String(value).padStart(2, '0');
            },

            destroy() {
                clearInterval(this.timer);
            },
        };
    }
</script>
