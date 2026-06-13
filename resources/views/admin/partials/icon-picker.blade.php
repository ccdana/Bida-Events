{{--
    Icon Picker Component — Admin
    Uso:
        @include('admin.partials.icon-picker', [
            'model'  => 'evento.icono',
            'icons'  => ['star','music',...],   // opcional, usa default si no se pasa
            'change' => 'schedulePreview()',     // opcional, callback al cambiar
        ])
--}}
@php
    $icons = $icons ?? ['star','glass','candle','dance','dinner','music','gift','crown','sparkle','camera','users','map-pin','calendar','shirt','poll'];
    $changeAction = $change ?? 'schedulePreview()';
@endphp

<div x-data="{ open: false }" class="relative" @keydown.escape.window="open = false">

    {{-- Trigger --}}
    <button type="button"
        @click="open = !open"
        @click.outside="open = false"
        class="flex items-center gap-2.5 w-full rounded-xl border border-stone-200 bg-stone-50 px-3 py-2 text-left hover:border-stone-300 hover:bg-white transition focus:outline-none focus:ring-2 focus:ring-amber-400/40">

        {{-- Cada icono como span visible/oculto según el valor del modelo --}}
        @foreach($icons as $iconName)
        <span x-show="{{ $model }} === '{{ $iconName }}'"
            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-white border border-stone-200 text-stone-600">
            @include('invitations.partials.icon', ['name' => $iconName, 'class' => 'w-4 h-4', 'animated' => false])
        </span>
        @endforeach
        {{-- Fallback: si no hay coincidencia --}}
        <span x-show="!{{ $model }} || !['{{ implode("','", $icons) }}'].includes({{ $model }})"
            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-stone-100 border border-stone-200 text-stone-400">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <circle cx="12" cy="12" r="9"/>
            </svg>
        </span>

        <span class="flex-1 text-sm text-stone-600" x-text="{{ $model }} || 'Seleccionar ícono'"></span>
        <svg class="w-4 h-4 text-stone-400 shrink-0 transition-transform duration-200"
            :class="open ? 'rotate-180' : ''"
            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    {{-- Dropdown grid de iconos --}}
    <div x-show="open" x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
        class="absolute left-0 right-0 top-full mt-1.5 z-40 rounded-2xl border border-stone-200 bg-white p-2 shadow-xl">
        <div class="grid grid-cols-5 gap-1">
            @foreach($icons as $iconName)
            <button type="button"
                @click="{{ $model }} = '{{ $iconName }}'; open = false; {{ $changeAction }}"
                :class="{{ $model }} === '{{ $iconName }}'
                    ? 'bg-amber-50 border-amber-400 text-amber-700'
                    : 'border-transparent text-stone-500 hover:bg-stone-100 hover:text-stone-800'"
                class="flex items-center justify-center rounded-xl border p-2.5 transition-all"
                title="{{ $iconName }}">
                @include('invitations.partials.icon', ['name' => $iconName, 'class' => 'w-5 h-5', 'animated' => false])
            </button>
            @endforeach
        </div>
    </div>
</div>
