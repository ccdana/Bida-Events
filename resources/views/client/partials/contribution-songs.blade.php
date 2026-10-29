{{--
    Canciones que sugieren los invitados para la playlist: casi siempre un video de YouTube (se ve su miniatura y se abre
    en YouTube) o el nombre de la canción escrito. En filas con filete; se pueden ocultar sin borrarlas (lo oculto deja de
    verse en la invitación). Recibe $invitation y $songs.
--}}
@include('client.partials.moderation-script')

<section class="grid grid-cols-[minmax(0,1fr)] gap-3" aria-labelledby="canciones-invitados">
    <h2 id="canciones-invitados" class="sr-only">Canciones y videos que sugirieron</h2>

    <p class="ev-meta" title="Pásale la lista a tu DJ. Lo que ocultes deja de verse en la invitación.">
        <span><b class="text-site-ink">{{ $songs->count() }}</b> {{ $songs->count() === 1 ? 'sugerencia' : 'sugerencias' }}</span>
        <span><x-phosphor-music-notes-light aria-hidden="true" /> Para tu DJ</span>
    </p>

    <ul class="ev-list">
        @foreach($songs as $item)
            <li class="ev-row eg-song" :class="hidden && 'is-past'"
                x-data="moderation(@js(route('client.contributions.update', [$invitation, $item['id']])), @js($item['isHidden']))">
                @if($item['youtubeId'])
                    <a href="https://www.youtube.com/watch?v={{ $item['youtubeId'] }}" target="_blank" rel="noopener"
                        class="eg-song__thumb" aria-label="Ver el video en YouTube">
                        <img src="https://i.ytimg.com/vi/{{ $item['youtubeId'] }}/mqdefault.jpg" alt="" loading="lazy" decoding="async">
                        <x-phosphor-play-fill aria-hidden="true" />
                    </a>
                @else
                    <span class="eg-song__thumb is-note"><x-phosphor-music-notes-light aria-hidden="true" /></span>
                @endif

                <div class="ev-row__main">
                    <h3 class="ev-row__name">{{ $item['youtubeId'] ? 'Video de YouTube' : $item['text'] }}</h3>
                    @if($item['youtubeId'])
                        <p class="truncate font-mono text-xs text-site-muted">{{ $item['text'] }}</p>
                    @endif
                    <p class="ev-meta">
                        <span>{{ $item['meta'] }}</span>
                        <span x-show="hidden" @if(! $item['isHidden']) x-cloak @endif class="font-medium text-site-ink">Oculta</span>
                    </p>
                </div>

                <form method="POST" action="{{ route('client.contributions.update', [$invitation, $item['id']]) }}" @submit.prevent="toggle()">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="moderation_status" value="{{ $item['isHidden'] ? 'visible' : 'hidden' }}">
                    <button type="submit" class="ev-act" :disabled="busy" :title="hidden ? 'Mostrar' : 'Ocultar'" :aria-label="hidden ? 'Mostrar esta sugerencia' : 'Ocultar esta sugerencia'">
                        <x-phosphor-eye-slash-light aria-hidden="true" x-show="!hidden" />
                        <x-phosphor-eye-light aria-hidden="true" x-show="hidden" x-cloak />
                    </button>
                    <span class="sr-only" role="status" x-text="message"></span>
                </form>
            </li>
        @endforeach
    </ul>
</section>
