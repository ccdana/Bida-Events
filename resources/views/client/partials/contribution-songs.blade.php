{{--
    Canciones que sugieren los invitados para la playlist: casi siempre un video de YouTube (se ve su
    miniatura y se abre en YouTube) o el nombre de la canción escrito. Se pueden ocultar sin borrarlas.
    Recibe $invitation y $songs.
--}}
<section class="site-enter mt-10 overflow-hidden rounded-[16px] border border-site-line bg-site-surface" style="--enter-index: 5" aria-labelledby="canciones-invitados">
    <div class="flex flex-wrap items-end justify-between gap-2 border-b border-site-line px-5 py-4">
        <div>
            <h2 id="canciones-invitados" class="text-lg font-semibold tracking-tight">Canciones y videos que sugirieron</h2>
            <p class="mt-1 text-sm text-site-muted">Pásale la lista a tu DJ. Lo que ocultes deja de verse en la invitación.</p>
        </div>
        <p class="text-sm text-site-muted"><span class="font-semibold text-site-ink">{{ $songs->count() }}</span> {{ $songs->count() === 1 ? 'sugerencia' : 'sugerencias' }}</p>
    </div>

    <ul class="divide-y divide-site-line">
        @foreach($songs as $item)
            <li class="flex items-center gap-4 px-5 py-3 {{ $item['isHidden'] ? 'opacity-60' : '' }}">
                @if($item['youtubeId'])
                    <a href="https://www.youtube.com/watch?v={{ $item['youtubeId'] }}" target="_blank" rel="noopener"
                        class="relative block w-24 shrink-0 overflow-hidden rounded-[10px] bg-site-tint" aria-label="Ver el video en YouTube">
                        <img src="https://i.ytimg.com/vi/{{ $item['youtubeId'] }}/mqdefault.jpg" alt="" loading="lazy" decoding="async" class="aspect-video w-full object-cover">
                        <span class="absolute inset-0 grid place-items-center">
                            <span class="grid size-7 place-items-center rounded-full bg-black/60 text-white">
                                <x-phosphor-play-fill class="size-3.5" aria-hidden="true" />
                            </span>
                        </span>
                    </a>
                @else
                    <span class="grid size-14 shrink-0 place-items-center rounded-[10px] bg-site-tint text-site-accent">
                        <x-phosphor-music-notes class="size-6" aria-hidden="true" />
                    </span>
                @endif

                <div class="min-w-0 flex-1">
                    <p class="truncate font-medium">{{ $item['youtubeId'] ? 'Video de YouTube' : $item['text'] }}</p>
                    @if($item['youtubeId'])
                        <p class="truncate font-mono text-xs text-site-muted">{{ $item['text'] }}</p>
                    @endif
                    <p class="mt-0.5 text-sm text-site-muted">
                        {{ $item['meta'] }}
                        @if($item['isHidden'])
                            <span class="font-medium text-site-ink">· Oculta</span>
                        @endif
                    </p>
                </div>

                <form method="POST" action="{{ route('client.contributions.update', [$invitation, $item['id']]) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="moderation_status" value="{{ $item['isHidden'] ? 'visible' : 'hidden' }}">
                    <button type="submit" class="admin-link-button">{{ $item['isHidden'] ? 'Mostrar' : 'Ocultar' }}</button>
                </form>
            </li>
        @endforeach
    </ul>
</section>
