{{--
    Iconos SVG animados — sin emojis.
    Uso: @include('invitations.partials.icon', ['name' => 'glass', 'class' => 'w-6 h-6'])
--}}
@php
    $class = $class ?? 'w-6 h-6';
    $animated = $animated ?? true;
    $animClass = $animated ? 'icon-animated' : '';
@endphp

@switch($name)
    @case('glass')
        <svg class="{{ $class }} {{ $animClass }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path d="M8 22h8M12 15v7M7 3h10l1 7H6L7 3z" stroke-linecap="round" stroke-linejoin="round"/>
            <path class="icon-draw" d="M8 10c0 2.2 1.8 4 4 4s4-1.8 4-4" stroke-linecap="round"/>
        </svg>
        @break
    @case('candle')
        <svg class="{{ $class }} {{ $animClass }} icon-flicker" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path d="M12 2c0 2-1.5 3-1.5 5a1.5 1.5 0 003 0C13.5 5 12 4 12 2z" fill="currentColor" stroke="none" opacity=".8"/>
            <rect x="10" y="9" width="4" height="12" rx="1"/>
            <line x1="9" y1="21" x2="15" y2="21"/>
        </svg>
        @break
    @case('dance')
        <svg class="{{ $class }} {{ $animClass }} icon-sway" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <circle cx="12" cy="4" r="2"/><path d="M12 6v4l-3 4 4 6-2 4M12 10l3 4-4 6 2 4" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        @break
    @case('dinner')
        <svg class="{{ $class }} {{ $animClass }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path d="M4 3v8a4 4 0 008 0V3M8 11v10M20 3v18" stroke-linecap="round"/>
        </svg>
        @break
    @case('music')
        <svg class="{{ $class }} {{ $animClass }} icon-bounce-soft" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/>
        </svg>
        @break
    @case('star')
        <svg class="{{ $class }} {{ $animClass }} icon-spin-slow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <polygon points="12 2 15 9 22 9 17 14 19 22 12 18 5 22 7 14 2 9 9 9" stroke-linejoin="round"/>
        </svg>
        @break
    @case('map-pin')
        <svg class="{{ $class }} {{ $animClass }} icon-bounce-soft" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path d="M12 21s7-4.5 7-11a7 7 0 10-14 0c0 6.5 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/>
        </svg>
        @break
    @case('calendar')
        <svg class="{{ $class }} {{ $animClass }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="16" y1="2" x2="16" y2="6"/>
        </svg>
        @break
    @case('camera')
        <svg class="{{ $class }} {{ $animClass }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path d="M4 7h3l2-3h6l2 3h3a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V9a2 2 0 012-2z"/><circle cx="12" cy="13" r="3"/>
        </svg>
        @break
    @case('play')
        <svg class="{{ $class }}" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
        @break
    @case('pause')
        <svg class="{{ $class }}" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6 5h4v14H6zm8 0h4v14h-4z"/></svg>
        @break
    @case('chevron-down')
        <svg class="{{ $class }} {{ $animClass }} icon-bounce-soft" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M6 9l6 6 6-6" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        @break
    @case('check')
        <svg class="{{ $class }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
            <path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        @break
    @case('close')
        <svg class="{{ $class }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M6 6l12 12M18 6L6 18" stroke-linecap="round"/>
        </svg>
        @break
    @case('users')
        <svg class="{{ $class }} {{ $animClass }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <circle cx="9" cy="7" r="3"/><path d="M3 21v-1a5 5 0 015-5h2a5 5 0 015 5v1M16 3.1a3 3 0 010 5.8M21 21v-1a4 4 0 00-3-3.9"/>
        </svg>
        @break
    @case('crown')
        <svg class="{{ $class }} {{ $animClass }} icon-float-soft" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path d="M3 18h18M5 18l2-10 5 5 5-5 2 10" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        @break
    @case('sparkle')
        <svg class="{{ $class }} {{ $animClass }} icon-spin-slow" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M12 2l1.5 5.5L19 9l-5.5 1.5L12 16l-1.5-5.5L5 9l5.5-1.5z" opacity=".9"/>
        </svg>
        @break
    @case('volume')
        <svg class="{{ $class }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path d="M11 5L6 9H3v6h3l5 4V5z"/><path d="M15 9a4 4 0 010 6M17 7a7 7 0 010 10" stroke-linecap="round"/>
        </svg>
        @break
    @case('shirt')
        <svg class="{{ $class }} {{ $animClass }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path d="M16 3l4 4-3 2v12H7V9L4 7l4-4 4 3 4-3z" stroke-linejoin="round"/>
        </svg>
        @break
    @case('poll')
        <svg class="{{ $class }} {{ $animClass }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <rect x="4" y="12" width="4" height="8" rx="1"/><rect x="10" y="8" width="4" height="12" rx="1"/><rect x="16" y="4" width="4" height="16" rx="1"/>
        </svg>
        @break
    @case('gift')
        <svg class="{{ $class }} {{ $animClass }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <rect x="3" y="8" width="18" height="13" rx="1"/><path d="M12 8v13M3 12h18M12 8c-2 0-3-1.5-3-3s1.5-2 3-2 3 1 3 2-1 3-3 3z" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        @break
    @default
        <svg class="{{ $class }} {{ $animClass }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <circle cx="12" cy="12" r="9"/>
        </svg>
@endswitch
