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
        <span class="inv-player__artist" x-text="playing || started ? @js($musica['artista'] ?? 'Sonando ahora') : 'Toca para escuchar'">{{ $invCopy['music_hint'] ?? 'Toca para escuchar' }}</span>
    </div>

    <span class="inv-player__eq" :class="{ 'is-playing': playing }" aria-hidden="true"><i></i><i></i><i></i></span>

    <label class="inv-player__volume">
        <span class="sr-only">Volumen</span>
        <input type="range" min="0" max="1" step="0.05" x-model.number="volume" @input="setVolume()">
    </label>

    <audio x-ref="audio" src="{{ $musica['audio_url'] }}" loop preload="none"></audio>
</div>
<script>
/*
 * Música de fondo. Con «reproducir automáticamente» intenta sonar apenas carga la página; los
 * navegadores lo bloquean hasta que el invitado toca algo, así que se queda escuchando los toques y
 * empieza con el primero que el navegador acepta (abrir la portada, tocar un botón, una tecla). Un
 * toque que el navegador no cuenta (deslizar, apoyar el dedo) no gasta el intento: sigue esperando.
 * Al cambiar de pestaña, minimizar, pasar a otra app o bloquear el teléfono se pausa, y al volver
 * sigue sonando si estaba sonando. Si el invitado la pausó él mismo, no vuelve sola.
 */
function musicPlayer(src, autoplay) {
    // Gestos que los navegadores aceptan para empezar a sonar
    const unlockEvents = ['pointerup', 'touchend', 'click', 'keydown'];

    return {
        playing: false,
        started: false,
        volume: 0.6,
        _unlockHandler: null,
        // Sonaba cuando el invitado salió: al volver, sigue sonando
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

            const leave = () => {
                if (!audio.paused) {
                    this._resumeOnReturn = true;
                    audio.pause();
                }
            };
            const comeBack = () => {
                if (this._resumeOnReturn && !document.hidden) {
                    this._resumeOnReturn = false;
                    this.play().catch(() => {});
                }
            };

            // Otra pestaña, otra app, pantalla bloqueada o ventana minimizada
            document.addEventListener('visibilitychange', () => (document.hidden ? leave() : comeBack()));

            // Otra ventana del escritorio encima (el navegador sigue visible pero ya no es el que se usa).
            // Dentro del editor la invitación es un iframe: ahí no, porque tocar el editor la pausaría.
            if (window.top === window) {
                window.addEventListener('blur', () => {
                    // Si el foco pasó a un video de la propia invitación (un iframe), no es irse
                    setTimeout(() => {
                        if (!document.hasFocus() && document.activeElement?.tagName !== 'IFRAME') {
                            leave();
                        }
                    }, 0);
                });
                window.addEventListener('focus', comeBack);
            }

            // Al irse del sitio (o guardarse en la caché de ir atrás) se detiene del todo
            window.addEventListener('pagehide', () => {
                this._resumeOnReturn = false;
                audio.pause();
            });

            // En las muestras de la home solo suena si el visitante toca reproducir
            if (!autoplay || window.invDemo) {
                return;
            }

            this.play().catch(() => this.waitForGesture());
        },
        /** Espera un gesto que el navegador acepte; se retira recién cuando la música empezó. */
        waitForGesture() {
            if (this._unlockHandler) {
                return;
            }

            this._unlockHandler = (event) => {
                // El botón del reproductor ya decide solo (tocarlo para pausar no debe hacerla sonar)
                if (event.target?.closest?.('.inv-player__toggle') || this.started) {
                    this.clearUnlock();
                    return;
                }

                this.play().then(() => this.clearUnlock()).catch(() => {});
            };

            // En captura: se entera del toque aunque el botón que lo recibe corte su propagación
            unlockEvents.forEach((name) => document.addEventListener(name, this._unlockHandler, { capture: true, passive: true }));
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

            unlockEvents.forEach((name) => document.removeEventListener(name, this._unlockHandler, { capture: true }));
            this._unlockHandler = null;
        },
        toggle() {
            this._resumeOnReturn = false;

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
