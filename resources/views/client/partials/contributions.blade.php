{{--
    Fotos del fotomural y canciones que dejan los invitados. Se pueden ocultar sin borrarlas.
    Recibe $invitation y $media.
--}}
<section class="site-enter mt-10 overflow-hidden rounded-[16px] border border-site-line bg-site-surface" style="--enter-index: 4">
    <div class="border-b border-site-line px-5 py-4">
        <h2 class="text-lg font-semibold tracking-tight">Fotos y canciones de tus invitados</h2>
        <p class="mt-1 text-sm text-site-muted">
            Si algo no te gusta, ocúltalo y deja de verse en la invitación. No se borra: puedes volver a mostrarlo.
        </p>
    </div>

    <ul class="divide-y divide-site-line">
        @foreach($media as $item)
            <li class="flex items-center gap-4 px-5 py-3 {{ $item['isHidden'] ? 'opacity-60' : '' }}">
                @if($item['url'])
                    <img src="{{ $item['url'] }}" alt="" class="size-14 shrink-0 rounded-[10px] object-cover" loading="lazy" decoding="async">
                @else
                    <span class="grid size-14 shrink-0 place-items-center rounded-[10px] bg-site-tint text-site-accent">
                        <x-phosphor-music-notes class="size-6" aria-hidden="true" />
                    </span>
                @endif

                <div class="min-w-0 flex-1">
                    @if($item['text'] !== '')
                        <p class="truncate font-medium">{{ $item['text'] }}</p>
                    @endif
                    <p class="mt-0.5 text-sm text-site-muted">
                        {{ $item['meta'] }}
                        @if($item['isHidden'])
                            <span class="font-medium text-site-ink">· Oculto</span>
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
