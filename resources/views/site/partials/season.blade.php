{{--
    Una temporada en la portada (Día del Amor, Halloween…): un botón que acompaña el scroll abajo a la
    derecha, con el nombre, el precio y los días que quedan. Cada temporada que se vende tiene el suyo,
    apilados ($seasonIndex); se encienden y apagan por separado desde Ajustes. Al tocarlo se abre el
    panel de la temporada (diseños,
    precio tachado y de promoción, cuenta regresiva y un teléfono donde el diseño elegido se abre solo),
    así la portada no se satura. «#temporada-{clave}» abre la suya y «#temporada», la primera.
    El panel habla de la temporada, no de un diseño: los diseños se listan y se van sumando en
    config('bida.seasons.{clave}.templates'). La cuenta llega hasta su «ends_at»; al terminar,
    el botón se oculta solo y pasada la fecha el servidor ya no lo manda.
    Recibe $season y $seasonIndex (HomeController::seasons). El teléfono carga su muestra recién al abrir el panel
    (data-lazy-src, resources/js/site.js). Estilos: resources/css/site/site.css («Temporada»).
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
    $seasonProduct = $season['product'] ?? 'tarjeta';
    $seasonPlural = $seasonProduct === 'tarjeta' ? 'Tarjetas' : 'Invitaciones';
    // Ícono del botón según la temporada; las que no tengan uno propio usan una estrella
    $seasonIcon = ['halloween' => 'ghost', 'amor' => 'heart'][$season['key'] ?? ''] ?? 'star-four';
    $seasonIndex ??= 0;
    $seasonId = 'temporada-'.$season['key'];
@endphp

<div class="site-season site-season--{{ $season['key'] ?? 'temporada' }}"
    x-data="seasonOffer(@js($season['endsAt']->toIso8601String()), @js(array_column($seasonDemos, 'demoUrl')), @js($seasonId), @js($seasonIndex === 0))"
    x-show="!expired"
    @keydown.escape.window="open && close()"
    @hashchange.window="fromHash()">

    {{-- Botón flotante: sigue al scroll y abre la temporada --}}
    <button type="button" class="site-season-fab" x-ref="fab" @click="show()" style="--fab-index: {{ $seasonIndex }}"
        aria-controls="{{ $seasonId }}" :aria-expanded="open.toString()" aria-expanded="false"
        x-show="!open" x-transition.opacity.duration.200ms>
        <span class="site-season-fab__icon" aria-hidden="true">
            <x-dynamic-component :component="'phosphor-'.$seasonIcon.'-fill'" />
        </span>
        <span class="site-season-fab__text">
            <span class="site-season-fab__name">{{ $season['name'] }}</span>
            <span class="site-season-fab__meta">
                {{ $season['final_price'] }} Bs ·
                <span x-text="left.days > 0 ? `quedan ${left.days} ${left.days === 1 ? 'día' : 'días'}` : 'último día'">quedan {{ $seasonUnits['days']['value'] }} días</span>
            </span>
        </span>
    </button>

    <div class="site-season__backdrop" x-show="open" x-cloak x-transition.opacity @click="close()"></div>

    <section id="{{ $seasonId }}" class="site-season__sheet" role="dialog" aria-modal="true" aria-labelledby="{{ $seasonId }}-titulo"
        x-show="open" x-cloak x-ref="sheet"
        x-transition:enter="site-season__sheet--enter" x-transition:enter-start="is-from" x-transition:enter-end="is-to"
        x-transition:leave="site-season__sheet--leave" x-transition:leave-start="is-to" x-transition:leave-end="is-from">
        <div class="site-season__panel">
            <span class="site-season__decor" aria-hidden="true">
                @for($piece = 0; $piece < 9; $piece++)
                    <i style="--i: {{ $piece }}"></i>
                @endfor
            </span>

            <button type="button" class="site-season__close" x-ref="close" @click="close()" aria-label="Cerrar la temporada">
                <x-phosphor-x aria-hidden="true" />
            </button>

            <div class="site-season__copy">
                <p class="site-season__eyebrow">{{ $seasonPlural }} de temporada · {{ $season['date'] ?? $seasonUntil }}</p>
                <h2 id="{{ $seasonId }}-titulo" class="site-season__title">{{ $season['title'] }}</h2>
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
                        <span class="site-season__label">Cada {{ $seasonProduct }}</span>
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
                        <a href="{{ $season['landingUrl'] }}" class="site-season__link">Ver todo sobre {{ $season['name'] }}</a>
                    @endif
                </div>
            </div>

            <div class="site-season__stage">
                <div class="site-phone site-phone--season" x-ref="reel"
                    data-cover-reel="{{ json_encode($seasonReel, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}"
                    data-cover-reel-replay data-cover-reel-dwell="15000">
                    <div class="site-phone__screen">
                        <iframe data-lazy-src="{{ $seasonDemos[0]['coverUrl'] }}" tabindex="-1" aria-hidden="true" data-cover-reel-frame
                            title="Muestra de la temporada"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@once
<script>
    /*
     * Temporada: el botón abre el panel (también un enlace a «#temporada»), la cuenta regresiva corre
     * y el teléfono carga su muestra la primera vez que se abre («reel:wake», resources/js/site.js).
     */
    function seasonOffer(endsAt, demos, id, first) {
        const end = new Date(endsAt).getTime();

        return {
            demos,
            id,
            active: 0,
            open: false,
            woke: false,
            expired: false,
            left: { days: 0, hours: 0, minutes: 0, seconds: 0 },
            timer: null,

            init() {
                this.tick();
                this.timer = setInterval(() => this.tick(), 1000);
                this.fromHash();
            },

            tick() {
                const ms = Math.max(end - Date.now(), 0);

                if (ms === 0) {
                    this.expired = true;
                    this.close();
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

            // «#temporada-{clave}» abre esta temporada; «#temporada» (el enlace de servicios), la primera
            ownsHash() {
                const hash = window.location.hash;

                return hash === `#${this.id}` || (first && hash === '#temporada');
            },

            fromHash() {
                if (this.ownsHash()) {
                    this.show();
                }
            },

            show() {
                if (this.expired) return;

                this.open = true;
                document.documentElement.classList.add('site-season-open');

                if (!this.woke) {
                    this.woke = true;
                    this.$refs.reel.dispatchEvent(new CustomEvent('reel:wake'));
                }

                this.$nextTick(() => this.$refs.close?.focus());
            },

            close() {
                if (!this.open) return;

                this.open = false;
                document.documentElement.classList.remove('site-season-open');

                // Se quita el ancla de la dirección para poder volver a abrirla con el mismo enlace
                if (this.ownsHash()) {
                    history.replaceState(null, '', window.location.pathname + window.location.search);
                }

                this.$nextTick(() => this.$refs.fab?.focus());
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
@endonce
