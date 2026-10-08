{{--
    Fotos que los invitados suben al fotomural, en una grilla como la de un perfil de Instagram: cuadros
    pegados, con el nombre de quien la subió. Se pueden ocultar sin borrarlas (y sin recargar la página):
    dejan de verse en la invitación y se vuelven a mostrar cuando se quiera. Recibe $invitation y $photos.
--}}
@include('client.partials.moderation-script')

<section class="site-enter mt-10 overflow-hidden rounded-[16px] border border-site-line bg-site-surface" style="--enter-index: 4" aria-labelledby="fotos-invitados"
    x-data="{ hiddenCount: {{ $photos->where('isHidden', true)->count() }} }"
    @moderated="hiddenCount += $event.detail.hidden ? 1 : -1">
    <div class="flex flex-wrap items-end justify-between gap-2 border-b border-site-line px-5 py-4">
        <div>
            <h2 id="fotos-invitados" class="text-lg font-semibold tracking-tight">Fotos de tus invitados</h2>
            <p class="mt-1 text-sm text-site-muted">Si una no te gusta, ocúltala con el ojo: deja de verse en la invitación, pero no se borra.</p>
        </div>
        <p class="text-sm text-site-muted">
            <span class="font-semibold text-site-ink">{{ $photos->count() }}</span> {{ $photos->count() === 1 ? 'foto' : 'fotos' }}
            <span x-show="hiddenCount > 0" x-cloak>· <span x-text="hiddenCount"></span> ocultas</span>
        </p>
    </div>

    <ul class="grid grid-cols-3 gap-0.5 p-0.5 md:grid-cols-4 md:gap-1 md:p-1">
        @foreach($photos as $item)
            <li class="group relative aspect-square overflow-hidden bg-site-tint"
                x-data="moderation(@js(route('client.contributions.update', [$invitation, $item['id']])), @js($item['isHidden']))">
                @if($item['url'])
                    <img src="{{ $item['url'] }}" alt="Foto de {{ $item['author'] ?? 'un invitado' }}" loading="lazy" decoding="async"
                        class="size-full object-cover transition duration-300" :class="hidden && 'opacity-30 grayscale'">
                @endif

                <p class="pointer-events-none absolute inset-x-0 bottom-0 truncate bg-gradient-to-t from-black/60 to-transparent px-2 pb-1.5 pt-6 text-[11px] font-medium text-white">
                    {{ $item['author'] ?? 'Invitado' }}
                </p>

                <span x-show="hidden" x-cloak class="pointer-events-none absolute left-1.5 top-1.5 rounded-full bg-black/65 px-2 py-0.5 text-[10px] font-medium uppercase tracking-wide text-white">Oculta</span>

                <form method="POST" action="{{ route('client.contributions.update', [$invitation, $item['id']]) }}" class="absolute right-1.5 top-1.5" @submit.prevent="toggle()">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="moderation_status" value="{{ $item['isHidden'] ? 'visible' : 'hidden' }}">
                    <button type="submit" :disabled="busy" class="grid size-8 place-items-center rounded-full bg-black/55 text-white backdrop-blur-sm transition-opacity hover:bg-black/75 disabled:opacity-50"
                        :aria-label="hidden ? 'Mostrar esta foto en la invitación' : 'Ocultar esta foto de la invitación'"
                        :title="hidden ? 'Mostrar' : 'Ocultar'">
                        <x-phosphor-eye-slash class="size-4" aria-hidden="true" x-show="!hidden" x-cloak />
                        <x-phosphor-eye class="size-4" aria-hidden="true" x-show="hidden" x-cloak />
                    </button>
                </form>

                <span class="sr-only" role="status" x-text="message"></span>
            </li>
        @endforeach
    </ul>
</section>
