{{--
    Temporadas en la portada (Día del Amor, Halloween…): un botoncito redondo abajo a la derecha que
    acompaña el scroll, sin texto (lo dice su aria-label). Lleva el ícono de cada temporada con su
    color (con varias, se turnan) y un punto que late; al tocarlo se abre una hoja con el panel de
    cada temporada (site/partials/season-panel). Con varias temporadas, cada panel lleva arriba el índice
    para pasar a la otra (con el teclado, también con las flechas).
    «#temporada» abre la primera y «#temporada-{clave}», la suya.

    Recibe $seasons (HomeController::seasons). Estilos: home.css («Botón de temporadas») y site.css
    («Temporada»).
--}}
@php
    $seasonIcons = ['halloween' => 'ghost', 'amor' => 'heart'];
    $soonest = collect($seasons)->sortBy(fn (array $season) => $season['endsAt']->getTimestamp())->first();
    $daysLeft = max(0, (int) now()->diffInDays($soonest['endsAt']));
    $fabLabel = (count($seasons) === 1 ? 'De temporada' : count($seasons).' temporadas').': '
        .collect($seasons)->pluck('name')->join(', ', ' y ').'. '
        .($daysLeft > 0 ? "Quedan {$daysLeft} ".($daysLeft === 1 ? 'día' : 'días') : 'Termina hoy');
@endphp

<div class="site-seasons" x-data="seasonDock(@js(array_column($seasons, 'key')))"
    @keydown.escape.window="open && close()" @hashchange.window="fromHash()">

    {{-- Botoncito: el ícono de cada temporada con su color y un punto que avisa que hay algo nuevo --}}
    <button type="button" class="site-seasons__fab" x-ref="fab" @click="show()" x-show="!open" x-transition.opacity.duration.200ms
        aria-controls="temporadas" :aria-expanded="open.toString()" aria-expanded="false"
        aria-label="{{ $fabLabel }}" title="{{ $fabLabel }}" @if(count($seasons) > 1) data-cycle @endif>
        @foreach($seasons as $season)
            <span class="site-seasons__orb site-seasons__orb--{{ $season['key'] }}" style="--i: {{ $loop->index }}" aria-hidden="true">
                <x-dynamic-component :component="'phosphor-'.($seasonIcons[$season['key']] ?? 'star-four').'-fill'" />
            </span>
        @endforeach
        <span class="site-seasons__ping" aria-hidden="true"></span>
    </button>

    <div class="site-season__backdrop" x-show="open" x-cloak x-transition.opacity @click="close()"></div>

    <section id="temporadas" class="site-season__sheet" role="dialog" aria-modal="true" aria-label="Diseños de temporada"
        x-show="open" x-cloak
        x-transition:enter="site-season__sheet--enter" x-transition:enter-start="is-from" x-transition:enter-end="is-to"
        x-transition:leave="site-season__sheet--leave" x-transition:leave-start="is-to" x-transition:leave-end="is-from">

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
    function seasonDock(keys) {
        return {
            keys,
            current: keys[0],
            open: false,
            root: null,

            init() {
                // En los métodos, $el es el botón que se tocó (o el panel): la raíz se guarda aquí
                this.root = this.$el;
                this.fromHash();
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
            },

            // Desde el índice de temporadas: el panel que se ve cambia, así que el foco pasa a la
            // pestaña de la temporada elegida en su propio panel
            pick(key) {
                this.select(key);
                this.$nextTick(() => this.root.querySelector(`[data-season="${key}"] .site-season__tab.is-active`)?.focus());
            },

            step(offset) {
                const index = this.keys.indexOf(this.current);
                this.pick(this.keys[(index + offset + this.keys.length) % this.keys.length]);
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
        };
    }

    /* Panel de una temporada: la cuenta regresiva hasta que termina */
    function seasonOffer(endsAt) {
        const end = new Date(endsAt).getTime();

        return {
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
