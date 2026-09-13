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
        init() {
            this.$refs.audio.volume = this.volume;

            if (!autoplay) {
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
