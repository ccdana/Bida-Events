@php
    $showMusicPlayer = ($flags['musica'] ?? false) && !empty($musica['audio_url'] ?? null);
@endphp
@if($showMusicPlayer && !empty($musica['audio_url']))
<div class="invitation-player"
    x-data="musicPlayer('{{ $musica['audio_url'] }}', {{ ($musica['autoplay'] ?? false) ? 'true' : 'false' }})"
    x-init="init()">

    {{-- Botón play/pause --}}
    <button type="button" @click="toggle()" aria-label="Reproducir o pausar"
        class="invitation-player__btn"
        :class="playing ? 'is-playing' : ''">
        <span x-show="!playing">@include('invitations.partials.icon', ['name' => 'play', 'class' => 'w-4 h-4', 'animated' => false])</span>
        <span x-show="playing" x-cloak>@include('invitations.partials.icon', ['name' => 'pause', 'class' => 'w-4 h-4', 'animated' => false])</span>
    </button>

    {{-- Info de la canción --}}
    <div class="invitation-player__info" @click="expanded = !expanded">
        <span class="invitation-player__title">{{ $musica['titulo'] ?? 'Música de fondo' }}</span>
        <span class="invitation-player__artist">{{ $musica['artista'] ?? '' }}</span>
    </div>

    {{-- Ecualizador visual --}}
    <div class="invitation-player__eq" x-show="playing" x-cloak aria-hidden="true">
        <span style="animation-delay:0ms"></span>
        <span style="animation-delay:150ms"></span>
        <span style="animation-delay:300ms"></span>
        <span style="animation-delay:450ms"></span>
    </div>

    {{-- Panel de volumen expandible --}}
    <div class="invitation-player__volume" x-show="expanded" x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2">
        @include('invitations.partials.icon', ['name' => 'volume', 'class' => 'w-3.5 h-3.5 invitation-player__vol-icon', 'animated' => false])
        <input type="range" min="0" max="1" step="0.05" x-model="volume" @input="setVolume()"
            class="invitation-player__slider">
    </div>

    <audio x-ref="audio" src="{{ $musica['audio_url'] }}" loop preload="metadata"></audio>
</div>
<script>
function musicPlayer(src, autoplay) {
    return {
        playing: false, expanded: false, volume: 0.6,
        _unlockHandler: null,
        init() {
            this.$refs.audio.volume = this.volume;
            if (autoplay) {
                // Intentar reproducir inmediatamente
                this.$refs.audio.play().then(() => {
                    this.playing = true;
                }).catch(() => {
                    // El navegador bloqueó el autoplay (política de interacción previa).
                    // Registrar un listener de un solo disparo para iniciar en el primer toque/clic.
                    this._unlockHandler = () => {
                        this.$refs.audio.play().then(() => {
                            this.playing = true;
                        }).catch(() => {});
                        // Remover el listener después del primer disparo
                        ['click','touchstart','keydown','scroll'].forEach(ev =>
                            document.removeEventListener(ev, this._unlockHandler)
                        );
                        this._unlockHandler = null;
                    };
                    ['click','touchstart','keydown','scroll'].forEach(ev =>
                        document.addEventListener(ev, this._unlockHandler, { once: true, passive: true })
                    );
                });
            }
        },
        toggle() {
            if (this.playing) {
                this.$refs.audio.pause();
                this.playing = false;
            } else {
                this.$refs.audio.play().then(() => {
                    this.playing = true;
                    // Si había un listener pendiente, ya no es necesario
                    if (this._unlockHandler) {
                        ['click','touchstart','keydown','scroll'].forEach(ev =>
                            document.removeEventListener(ev, this._unlockHandler)
                        );
                        this._unlockHandler = null;
                    }
                }).catch(() => {});
            }
        },
        setVolume() { this.$refs.audio.volume = this.volume; }
    };
}
</script>
@endif
