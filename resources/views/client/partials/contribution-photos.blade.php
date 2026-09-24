{{--
    Fotos que los invitados suben al fotomural, en grilla. Se pueden ocultar sin borrarlas: dejan de
    verse en la invitación y se vuelven a mostrar cuando se quiera. Recibe $invitation y $photos.
--}}
<section class="site-enter mt-10 overflow-hidden rounded-[16px] border border-site-line bg-site-surface" style="--enter-index: 4" aria-labelledby="fotos-invitados">
    <div class="flex flex-wrap items-end justify-between gap-2 border-b border-site-line px-5 py-4">
        <div>
            <h2 id="fotos-invitados" class="text-lg font-semibold tracking-tight">Fotos de tus invitados</h2>
            <p class="mt-1 text-sm text-site-muted">Si una no te gusta, ocúltala: deja de verse en la invitación, pero no se borra.</p>
        </div>
        <p class="text-sm text-site-muted"><span class="font-semibold text-site-ink">{{ $photos->count() }}</span> {{ $photos->count() === 1 ? 'foto' : 'fotos' }}</p>
    </div>

    <ul class="grid grid-cols-2 gap-3 p-4 sm:grid-cols-3 lg:grid-cols-4">
        @foreach($photos as $item)
            <li class="group relative overflow-hidden rounded-[12px] bg-site-tint">
                @if($item['url'])
                    <img src="{{ $item['url'] }}" alt="Foto de {{ $item['author'] ?? 'un invitado' }}" loading="lazy" decoding="async"
                        class="aspect-square w-full object-cover transition-opacity {{ $item['isHidden'] ? 'opacity-40 grayscale' : '' }}">
                @endif
                <div class="flex items-center justify-between gap-2 px-2.5 py-2">
                    <p class="min-w-0 truncate text-xs text-site-muted">
                        {{ $item['author'] ?? 'Invitado' }}
                        @if($item['isHidden'])
                            <span class="font-medium text-site-ink">· Oculta</span>
                        @endif
                    </p>
                    <form method="POST" action="{{ route('client.contributions.update', [$invitation, $item['id']]) }}" class="shrink-0">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="moderation_status" value="{{ $item['isHidden'] ? 'visible' : 'hidden' }}">
                        <button type="submit" class="text-xs font-medium underline underline-offset-2">{{ $item['isHidden'] ? 'Mostrar' : 'Ocultar' }}</button>
                    </form>
                </div>
            </li>
        @endforeach
    </ul>
</section>
