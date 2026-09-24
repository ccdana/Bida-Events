{{--
    Temporadas en la portada (Día del Amor, Halloween…): un solo botón discreto abajo a la derecha que
    acompaña el scroll. Muestra la temporada (o cuántas hay) con su color y los días que quedan; al
    tocarlo se abre una hoja con el panel de cada temporada (site/partials/season-panel). Con varias
    temporadas, arriba se elige cuál ver. «#temporada» abre la primera y «#temporada-{clave}», la suya.

    Recibe $seasons (HomeController::seasons). Cada panel carga su muestra recién al mostrarse
    (data-lazy-src, «reel:wake» en resources/js/site.js). Estilos: site.css («Temporada»).
--}}
@php
    $seasonIcons = ['halloween' => 'ghost', 'amor' => 'heart'];
    $soonest = collect($seasons)->sortBy(fn (array $season) => $season['endsAt']->getTimestamp())->first();
@endphp

<div class="site-seasons" x-data="seasonDock(@js(array_column($seasons, 'key')), @js($soonest['endsAt']->toIso8601String()))"
    @keydown.escape.window="open && close()" @hashchange.window="fromHash()">

    {{-- Botón: el color de cada temporada, su nombre y cuánto falta para que termine la más próxima --}}
    <button type="button" class="site-seasons__fab" x-ref="fab" @click="show()" x-show="!open" x-transition.opacity.duration.200ms
        aria-controls="temporadas" :aria-expanded="open.toString()" aria-expanded="false">
        <span class="site-seasons__orbs" aria-hidden="true">
            @foreach($seasons as $season)
                <span class="site-seasons__orb site-seasons__orb--{{ $season['key'] }}">
                    <x-dynamic-component :component="'phosphor-'.($seasonIcons[$season['key']] ?? 'star-four').'-fill'" />
                </span>
            @endforeach
        </span>
        <span class="site-seasons__label">
            <span class="site-seasons__kicker">{{ count($seasons) === 1 ? 'De temporada' : count($seasons).' temporadas' }}</span>
            <span class="site-seasons__name">{{ collect($seasons)->pluck('name')->join(' · ') }}</span>
        </span>
        <span class="site-seasons__days">
            <span x-text="left.days > 0 ? left.days : 'Hoy'">{{ max(0, (int) now()->diffInDays($soonest['endsAt'])) }}</span>
            <small x-show="left.days > 0">días</small>
        </span>
    </button>

    <div class="site-season__backdrop" x-show="open" x-cloak x-transition.opacity @click="close()"></div>

    <section id="temporadas" class="site-season__sheet" role="dialog" aria-modal="true" aria-label="Diseños de temporada"
        x-show="open" x-cloak
        x-transition:enter="site-season__sheet--enter" x-transition:enter-start="is-from" x-transition:enter-end="is-to"
        x-transition:leave="site-season__sheet--leave" x-transition:leave-start="is-to" x-transition:leave-end="is-from">

        @if(count($seasons) > 1)
            <div class="site-seasons__tabs" role="tablist" aria-label="Temporadas">
                @foreach($seasons as $season)
                    <button type="button" role="tab" @click="select(@js($season['key']))"
                        :aria-selected="(current === @js($season['key'])).toString()" aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                        :class="{ 'is-active': current === @js($season['key']) }" @class(['site-seasons__tab', 'is-active' => $loop->first])>
                        {{ $season['name'] }}
                    </button>
                @endforeach
            </div>
        @endif

        @foreach($seasons as $season)
            @include('site.partials.season-panel', ['season' => $season])
        @endforeach
    </section>
</div>

@once
<script>
    /*
     * Botón de temporadas: abre la hoja (también un enlace a «#temporada» o «#temporada-{clave}»),
     * elige qué temporada se ve y despierta su teléfono la primera vez que se muestra.
     */
    function seasonDock(keys, endsAt) {
        const end = new Date(endsAt).getTime();

        return {
            keys,
            current: keys[0],
            open: false,
            left: { days: 0 },
            woken: {},
            root: null,
            timer: null,

            init() {
                // En los métodos, $el es el botón que se tocó (o el panel): la raíz se guarda aquí
                this.root = this.$el;
                this.tick();
                this.timer = setInterval(() => this.tick(), 60000);
                this.fromHash();
            },

            tick() {
                this.left = { days: Math.floor(Math.max(end - Date.now(), 0) / 86400000) };
            },

            keyFromHash() {
                const hash = window.location.hash.replace('#', '');

                if (hash === 'temporada' || hash === 'temporadas') return this.keys[0];

                return this.keys.find((key) => hash === `temporada-${key}`) ?? null;
            },

            fromHash() {
                const key = this.keyFromHash();
                if (key) this.show(key);
            },

            show(key = this.current) {
                this.select(key);
                this.open = true;
                document.documentElement.classList.add('site-season-open');
                this.$nextTick(() => this.root.querySelector(`[data-season="${this.current}"] .site-season__close`)?.focus());
            },

            select(key) {
                this.current = key;

                if (!this.woken[key]) {
                    this.woken[key] = true;
                    this.$nextTick(() => this.root.querySelector(`[data-season="${key}"] [data-cover-reel]`)?.dispatchEvent(new CustomEvent('reel:wake')));
                }
            },

            close() {
                if (!this.open) return;

                this.open = false;
                document.documentElement.classList.remove('site-season-open');

                // Se quita el ancla de la dirección para poder volver a abrir con el mismo enlace
                if (this.keyFromHash()) {
                    history.replaceState(null, '', window.location.pathname + window.location.search);
                }

                this.$nextTick(() => this.root.querySelector('.site-seasons__fab')?.focus());
            },

            destroy() {
                clearInterval(this.timer);
            },
        };
    }

    /* Panel de una temporada: cuenta regresiva y el diseño elegido en su teléfono (evento «reel:go») */
    function seasonOffer(endsAt, demos) {
        const end = new Date(endsAt).getTime();

        return {
            demos,
            active: 0,
            left: { days: 0, hours: 0, minutes: 0, seconds: 0 },
            timer: null,

            init() {
                this.tick();
                this.timer = setInterval(() => this.tick(), 1000);
            },

            tick() {
                const seconds = Math.floor(Math.max(end - Date.now(), 0) / 1000);
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
@endonce
