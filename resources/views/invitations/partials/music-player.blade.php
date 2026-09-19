@php
    $showMusicPlayer = ($flags['musica'] ?? false) && !empty($musica['audio_url'] ?? null);
@endphp
@if($showMusicPlayer)
<div class="inv-player"
    x-data="musicPlayer(@js($musica['audio_url']), @js((bool) ($musica['autoplay'] ?? false)))"
    x-init="init()">

    <button type="button" class="inv-player__toggle" @click="toggle()"
        :aria-label="playing ? 'Pausar música' : 'Reproducir música'"
        :aria-pressed="playing.toString()">
        <span x-show="!playing">@include('invitations.partials.icon', ['name' => 'play', 'animated' => false])</span>
        <span x-show="playing" x-cloak>@include('invitations.partials.icon', ['name' => 'pause', 'animated' => false])</span>
    </button>

    <div class="inv-player__meta">
        <span class="inv-player__title">{{ $musica['titulo'] ?? 'Música de fondo' }}</span>
        <span class="inv-player__artist" x-text="playing || started ? @js($musica['artista'] ?? 'Sonando ahora') : 'Toca para escuchar'">Toca para escuchar</span>
    </div>

    <span class="inv-player__eq" :class="{ 'is-playing': playing }" aria-hidden="true"><i></i><i></i><i></i></span>

    <label class="inv-player__volume">
        <span class="sr-only">Volumen</span>
        <input type="range" min="0" max="1" step="0.05" x-model.number="volume" @input="setVolume()">
    </label>

    <audio x-ref="audio" src="{{ $musica['audio_url'] }}" loop preload="none"></audio>
</div>
<script>
function musicPlayer(src, autoplay) {
    const unlockEvents = ['click', 'touchstart', 'keydown', 'scroll'];

    return {
        playing: false,
        started: false,
        volume: 0.6,
        _unlockHandler: null,
        // Sonaba cuando el invitado salió de la página: al volver, sigue sonando
        _resumeOnReturn: false,
        _wired: false,
        init() {
            // Alpine llama a init() solo y la vista además lo pide con x-init: sin esto, los
            // escuchas de abajo se registrarían dos veces y el segundo desharía al primero.
            if (this._wired) {
                return;
            }

            this._wired = true;

            const audio = this.$refs.audio;
            audio.volume = this.volume;

            // El botón refleja lo que pasa de verdad (también si se pausa desde la pantalla de bloqueo)
            audio.addEventListener('play', () => { this.playing = true; this.started = true; });
            audio.addEventListener('pause', () => { this.playing = false; });

            // En el celular, cambiar de app, de pestaña o bloquear la pantalla no deja la música
            // sonando de fondo: se pausa al salir y retoma al volver si estaba sonando.
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    this._resumeOnReturn = !audio.paused;
                    audio.pause();
                } else if (this._resumeOnReturn) {
                    this._resumeOnReturn = false;
                    this.play().catch(() => {});
                }
            });

            // Al irse del sitio (o guardarse en la caché de ir atrás) se detiene del todo
            window.addEventListener('pagehide', () => {
                this._resumeOnReturn = false;
                audio.pause();
            });

            // En las muestras de la home solo suena si el visitante toca reproducir
            if (!autoplay || window.invDemo) {
                return;
            }

            this.play().catch(() => {
                // El navegador bloquea el autoplay hasta la primera interacción
                this._unlockHandler = () => {
                    this.play().catch(() => {});
                    this.clearUnlock();
                };
                unlockEvents.forEach((event) => document.addEventListener(event, this._unlockHandler, { once: true, passive: true }));
            });
        },
        play() {
            return this.$refs.audio.play().then(() => {
                this.playing = true;
                this.started = true;
            });
        },
        clearUnlock() {
            if (!this._unlockHandler) {
                return;
            }

            unlockEvents.forEach((event) => document.removeEventListener(event, this._unlockHandler));
            this._unlockHandler = null;
        },
        toggle() {
            if (this.playing) {
                this.$refs.audio.pause();
                this.playing = false;
                return;
            }

            this.play().then(() => this.clearUnlock()).catch(() => {});
        },
        setVolume() {
            this.$refs.audio.volume = this.volume;
        }
    };
}
</script>
@endif
