{{--
    Selector de íconos del itinerario: cada opción muestra el dibujo y el nombre del momento.
    Uso: @include('admin.partials.itinerary-icon-picker', ['model' => 'evento.icono', 'change' => 'schedulePreview()'])
--}}
@php
    $iconGroups = \App\Support\ItineraryIcons::groups();
    $iconLabels = \App\Support\ItineraryIcons::all();
    $changeAction = $change ?? 'schedulePreview()';
@endphp

<div class="relative"
    x-data="{
        open: false,
        labels: @js($iconLabels),
        aliases: @js(\App\Support\ItineraryIcons::aliases()),
        resolve(value) {
            const key = this.aliases[value] ?? value;
            return this.labels[key] ? key : @js(\App\Support\ItineraryIcons::DEFAULT);
        },
    }"
    @keydown.escape.window="open = false"
    @click.outside="open = false">

    <button type="button"
        @click="open = !open"
        :aria-expanded="open.toString()"
        class="flex items-center gap-2.5 w-full rounded-xl border border-stone-200 bg-stone-50 px-3 py-2 text-left hover:border-stone-300 hover:bg-white transition focus:outline-none focus:ring-2 focus:ring-amber-400/40">
        @foreach($iconLabels as $key => $label)
            <span x-show="resolve({{ $model }}) === '{{ $key }}'"
                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white border border-stone-200 text-stone-700">
                @include('invitations.partials.itinerary-icon', ['name' => $key, 'class' => 'w-5 h-5'])
            </span>
        @endforeach
        <span class="min-w-0 flex-1">
            <span class="block truncate text-sm text-stone-800" x-text="labels[resolve({{ $model }})]"></span>
            <span class="block text-[11px] text-stone-400">Toca para cambiar el ícono</span>
        </span>
        <x-phosphor-caret-down class="w-4 h-4 text-stone-400 shrink-0 transition-transform duration-200" x-bind:class="open ? 'rotate-180' : ''" aria-hidden="true" />
    </button>

    <div x-show="open" x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        class="absolute left-0 right-0 top-full z-40 mt-1.5 max-h-80 overflow-y-auto rounded-2xl border border-stone-200 bg-white p-2 shadow-xl">
        @foreach($iconGroups as $groupLabel => $icons)
            <p class="px-1.5 pb-1 pt-2 text-[10px] font-semibold uppercase tracking-wider text-stone-400">{{ $groupLabel }}</p>
            <div class="grid grid-cols-2 gap-1">
                @foreach($icons as $key => $label)
                    <button type="button"
                        @click="{{ $model }} = '{{ $key }}'; open = false; {{ $changeAction }}"
                        :class="resolve({{ $model }}) === '{{ $key }}'
                            ? 'bg-amber-50 border-amber-300 text-amber-800'
                            : 'border-transparent text-stone-600 hover:bg-stone-100 hover:text-stone-900'"
                        class="flex items-center gap-2 rounded-lg border px-2 py-2 text-left text-xs transition">
                        @include('invitations.partials.itinerary-icon', ['name' => $key, 'class' => 'w-5 h-5 shrink-0'])
                        <span class="truncate">{{ $label }}</span>
                    </button>
                @endforeach
            </div>
        @endforeach
    </div>
</div>
