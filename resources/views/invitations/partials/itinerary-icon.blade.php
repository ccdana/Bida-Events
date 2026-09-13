{{--
    Íconos de los momentos del itinerario (catálogo en App\Support\ItineraryIcons).
    Todos comparten grilla de 24 px, trazo de 1.5 y puntas redondeadas.
    Uso: @include('invitations.partials.itinerary-icon', ['name' => 'vals', 'class' => 'w-5 h-5'])
--}}
@php($iconKey = \App\Support\ItineraryIcons::resolve($name ?? null))
<svg class="{{ $class ?? 'w-5 h-5' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($iconKey)
        @case('traslado')
            <path d="M6.5 16.5h-3a.5.5 0 01-.5-.5v-2.7a1.5 1.5 0 01.9-1.4L6 11l2.3-3.4A1.5 1.5 0 019.5 7h5.3a1.5 1.5 0 011.1.5L19 11l1.3.4a1.5 1.5 0 011.2 1.5V16a.5.5 0 01-.5.5h-1.5"/>
            <path d="M6 11h13M11.5 7v4M10.5 16.5h5"/>
            <circle cx="8.5" cy="16.5" r="2"/>
            <circle cx="17.5" cy="16.5" r="2"/>
            @break
        @case('recepcion')
            <circle cx="9" cy="8" r="3"/>
            <path d="M3.5 20a5.5 5.5 0 0111 0"/>
            <path d="M16 5.2a3 3 0 010 5.6M17.5 14.3a5.5 5.5 0 013 5.7"/>
            @break
        @case('ceremonia')
            <path d="M12 2.5v4M10 4.5h4"/>
            <path d="M5.5 21V11.5L12 6.5l6.5 5V21"/>
            <path d="M3 21h18M10 21v-3.5a2 2 0 014 0V21"/>
            @break
        @case('entrada')
            <path d="M4.5 18l-1-9.5 4.5 4 4-7 4 7 4.5-4-1 9.5z"/>
            <path d="M4.5 21h15"/>
            <circle cx="12" cy="3.5" r="1"/>
            @break
        @case('vals')
            <circle cx="8" cy="4.5" r="2"/>
            <circle cx="16" cy="4.5" r="2"/>
            <path d="M8 7.5V14l-2 7M8 14l2 7"/>
            <path d="M16 7.5l-3.5 13.5h7z"/>
            <path d="M8 10.5h8"/>
            @break
        @case('velas')
            <path d="M3 21h18"/>
            <rect x="4.5" y="13.5" width="3" height="7.5" rx="0.5"/>
            <rect x="10.5" y="10.5" width="3" height="10.5" rx="0.5"/>
            <rect x="16.5" y="13.5" width="3" height="7.5" rx="0.5"/>
            <path d="M6 9c-.9 1.1-.9 2.3 0 3 .9-.7.9-1.9 0-3zM12 6c-.9 1.1-.9 2.3 0 3 .9-.7.9-1.9 0-3zM18 9c-.9 1.1-.9 2.3 0 3 .9-.7.9-1.9 0-3z"/>
            @break
        @case('zapatilla')
            <path d="M4.5 7.5V19h2l1-5.2"/>
            <path d="M4.5 7.5c3.6 0 6.2 3.4 9.2 6 2 1.7 4 2.3 6 2.8.5.1.8.6.8 1.1V19H11l-3.5-5.2"/>
            @break
        @case('muneca')
            <circle cx="12" cy="5" r="2.5"/>
            <path d="M12 7.5v2M9.5 9.5h5l2.5 9.5H7z"/>
            <path d="M9.5 11l-3 3.5M14.5 11l3 3.5M10 19v2.5M14 19v2.5"/>
            @break
        @case('baile')
            <path d="M11 3l1.8 4.9 5.2 1.6-5.2 1.6L11 16l-1.8-4.9L4 9.5l5.2-1.6z"/>
            <path d="M18.5 14.5v5M16 17h5M5.5 17v3M4 18.5h3"/>
            @break
        @case('brindis')
            <g transform="rotate(-14 7.5 12)">
                <path d="M5.5 4h4l-.4 5.2a1.6 1.6 0 01-3.2 0z"/>
                <path d="M7.5 10.8V19M5.5 19h4"/>
            </g>
            <g transform="rotate(14 16.5 12)">
                <path d="M14.5 4h4l-.4 5.2a1.6 1.6 0 01-3.2 0z"/>
                <path d="M16.5 10.8V19M14.5 19h4"/>
            </g>
            <path d="M12 2v1.5M10.3 2.8l-.8-.8M13.7 2.8l.8-.8"/>
            @break
        @case('cena')
            <circle cx="12" cy="12.5" r="4.5"/>
            <path d="M3.5 4v4.5a1.5 1.5 0 003 0V4M5 4v16"/>
            <path d="M20.5 20V4c-1.7 1.2-2.7 3.3-2.7 6.2V13h2.7"/>
            @break
        @case('pastel')
            <path d="M3.5 21h17M5 21v-6h14v6M7.5 15v-4.5h9V15"/>
            <path d="M12 10.5V8M12 4.5c-.8.9-.8 1.9 0 2.4.8-.5.8-1.5 0-2.4z"/>
            <path d="M5 17.5c1.2.8 2.3.8 3.5 0s2.3-.8 3.5 0 2.3.8 3.5 0 2.3-.8 3.5 0"/>
            @break
        @case('musica')
            <rect x="9" y="2.5" width="6" height="11" rx="3"/>
            <path d="M5.5 11a6.5 6.5 0 0013 0M12 17.5V21M9 21h6"/>
            @break
        @case('fiesta')
            <path d="M12 2v3"/>
            <circle cx="12" cy="13" r="8"/>
            <path d="M4 13h16M5.4 8.5h13.2M5.4 17.5h13.2"/>
            <path d="M12 5c-2.4 2-3.5 5-3.5 8s1.1 6 3.5 8M12 5c2.4 2 3.5 5 3.5 8s-1.1 6-3.5 8"/>
            @break
        @case('hora-loca')
            <path d="M4 20.5l4.5-12 7.5 7.5z"/>
            <path d="M13 5.5c.6-1 .5-2-.2-2.6M17.2 9.2c1.1-.5 2.1-.2 2.6.6M14.5 8l3.5-3.5M19.5 14l1.5.4M9.8 4.4 9.4 3"/>
            @break
        @case('fotos')
            <path d="M4 7.5h3l1.8-2.5h6.4L17 7.5h3a1.5 1.5 0 011.5 1.5v9.5A1.5 1.5 0 0120 20H4a1.5 1.5 0 01-1.5-1.5V9A1.5 1.5 0 014 7.5z"/>
            <circle cx="12" cy="13.5" r="3.5"/>
            @break
        @case('sorpresa')
            <rect x="3.5" y="9" width="17" height="4" rx="0.5"/>
            <path d="M5 13v8h14v-8M12 9v12"/>
            <path d="M12 9c-1.5-3.5-5.5-4.5-5.5-2 0 1.5 2.5 2 5.5 2zM12 9c1.5-3.5 5.5-4.5 5.5-2 0 1.5-2.5 2-5.5 2z"/>
            @break
        @case('despedida')
            <path d="M12 3a6.5 6.5 0 009 9 9 9 0 11-9-9z"/>
            <path d="M17.5 3v3M16 4.5h3"/>
            @break
        @default
            <path d="M12 3.5l2.6 5.3 5.9.9-4.3 4.1 1 5.8L12 16.9l-5.2 2.7 1-5.8-4.3-4.1 5.9-.9z"/>
    @endswitch
</svg>
