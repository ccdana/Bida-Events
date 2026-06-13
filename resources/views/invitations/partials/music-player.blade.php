@php
    $showMusicPlayer = ($flags['musica'] ?? false) && !empty($musica['audio_url'] ?? null);
@endphp
@if($showMusicPlayer && !empty($musica['audio_url']))
<div class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 w-[calc(100%-2rem)] max-w-sm"
    x-data="musicPlayer('{{ $musica['audio_url'] }}', {{ ($musica['autoplay'] ?? false) ? 'true' : 'false' }})"
    x-init="init()">
    <div class="rounded-2xl border border-primary/20 bg-white/90 backdrop-blur-md shadow-2xl overflow-hidden transition-all duration-300"
        :class="expanded ? 'pb-3' : ''">
        {{-- Barra compacta --}}
        <div class="flex items-center gap-3 px-4 py-3">
            <button type="button" @click="toggle()" aria-label="Reproducir o pausar"
                class="shrink-0 w-11 h-11 rounded-full bg-primary text-white flex items-center justify-center shadow-md transition-transform active:scale-95">
                <span x-show="!playing">@include('invitations.partials.icon', ['name' => 'play', 'class' => 'w-5 h-5', 'animated' => false])</span>
                <span x-show="playing" x-cloak>@include('invitations.partials.icon', ['name' => 'pause', 'class' => 'w-5 h-5', 'animated' => false])</span>
            </button>

            <button type="button" @click="expanded = !expanded" class="flex-1 min-w-0 text-left">
                <p class="text-xs uppercase tracking-wider text-primary/70 truncate">{{ $musica['titulo'] ?? 'Música de fondo' }}</p>
                <p class="text-sm font-medium truncate opacity-80">{{ $musica['artista'] ?? 'Toca para escuchar' }}</p>
            </button>

            {{-- Visualizador animado --}}
            <div class="flex items-end gap-0.5 h-5 shrink-0" x-show="playing" x-cloak aria-hidden="true">
                <span class="w-0.5 bg-primary rounded-full animate-eq" style="animation-delay:0ms;height:60%"></span>
                <span class="w-0.5 bg-primary rounded-full animate-eq" style="animation-delay:150ms;height:100%"></span>
                <span class="w-0.5 bg-primary rounded-full animate-eq" style="animation-delay:300ms;height:40%"></span>
                <span class="w-0.5 bg-primary rounded-full animate-eq" style="animation-delay:450ms;height:80%"></span>
            </div>
        </div>

        {{-- Controles expandidos --}}
        <div x-show="expanded" x-cloak class="px-4 pb-1 space-y-3 border-t border-primary/10 pt-3">
            <div class="flex items-center gap-3">
                @include('invitations.partials.icon', ['name' => 'volume', 'class' => 'w-4 h-4 text-primary/60', 'animated' => false])
                <input type="range" min="0" max="1" step="0.05" x-model="volume" @input="setVolume()"
                    class="flex-1 h-1 accent-[var(--primary-color)] rounded-full">
            </div>
            <p class="text-[10px] text-center opacity-40 tracking-wide">La música continúa en segundo plano mientras navegas</p>
        </div>
    </div>
    <audio x-ref="audio" src="{{ $musica['audio_url'] }}" loop preload="metadata"></audio>
</div>
<script>
function musicPlayer(src, autoplay) {
    return {
        playing: false, expanded: false, volume: 0.6,
        init() {
            this.$refs.audio.volume = this.volume;
            if (autoplay) this.toggle();
        },
        toggle() {
            this.playing = !this.playing;
            this.playing ? this.$refs.audio.play().catch(() => {}) : this.$refs.audio.pause();
        },
        setVolume() { this.$refs.audio.volume = this.volume; }
    };
}
</script>
@endif
