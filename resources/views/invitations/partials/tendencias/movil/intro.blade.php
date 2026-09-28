{{--
    Apertura de «Móvil de cuna»: el cuarto del bebé de noche, con la ventana y su luna, y el móvil
    quieto sobre la baranda de la cuna: un aro de madera con las figuras de fieltro colgando alrededor,
    que se mece apenas. Al tocarlo, el móvil gira como uno de verdad (las figuras dan la vuelta al aro,
    las de adelante más grandes que las de atrás), salen notas de la cajita de música y la lamparita
    proyecta estrellas que giran por la pared; después la luz del cuarto se enciende desde el móvil y
    queda la portada. Lógica en shell/cover-component; estilos en tendencias/movil.css.
--}}
@php
    // Las estrellas que proyecta la lamparita: posición (%), tamaño (rem) y retraso
    $projected = [
        [12, 14, 0.9, 0], [28, 8, 0.6, 0.2], [78, 12, 1.1, 0.1], [88, 30, 0.7, 0.35], [8, 42, 0.8, 0.25],
        [22, 64, 1, 0.15], [70, 58, 0.6, 0.3], [90, 70, 0.9, 0.05], [46, 6, 0.7, 0.4], [60, 80, 0.8, 0.2],
        [36, 86, 0.6, 0.1], [84, 88, 0.7, 0.3],
    ];
@endphp

<div class="inv-themed-intro mv-intro"
    x-data="invitationCover({ part: 2300, reveal: 2800, close: 3500 })"
    x-show="!closed"
    :class="{ 'is-spinning': stage >= 1, 'is-open': stage >= 2 }"
    role="dialog"
    aria-modal="true"
    aria-label="Invitación al bautizo de {{ $page->displayName }}">
    {{-- La ventana del cuarto, con la luna afuera --}}
    <span class="mv-intro__window" aria-hidden="true"><i></i></span>

    {{-- Las estrellas que proyecta la lamparita al sonar la música --}}
    <span class="mv-intro__projector" aria-hidden="true">
        @foreach($projected as [$x, $y, $size, $delay])
            <i style="--x: {{ $x }}%; --y: {{ $y }}%; --s: {{ $size }}rem; --delay: {{ $delay }}s"></i>
        @endforeach
    </span>

    <p class="mv-intro__eyebrow">
        @if($guest)
            Para {{ $guest->name }}
        @else
            {{ $invCopy['intro_eyebrow'] ?? 'Un día muy especial' }}
        @endif
    </p>

    <button type="button" class="mv-intro__trigger" data-cover-trigger @click="open()" aria-label="Hacer girar el móvil y abrir la invitación">
        @include('invitations.partials.tendencias.movil.mobile', ['class' => 'mv-mobile--intro'])
        {{-- Las notas de la cajita de música --}}
        <span class="mv-intro__notes" aria-hidden="true">
            @foreach(['♪', '♫', '♪', '♩', '♫', '♪'] as $note)
                <i style="--i: {{ $loop->index }}">{{ $note }}</i>
            @endforeach
        </span>
    </button>

    {{-- La baranda de la cuna --}}
    <svg class="mv-crib" viewBox="0 0 320 70" preserveAspectRatio="none" aria-hidden="true" focusable="false">
        <rect x="0" y="4" width="320" height="10" rx="5"/>
        @for($bar = 0; $bar < 11; $bar++)
            <rect x="{{ 14 + $bar * 29 }}" y="12" width="7" height="58" rx="3"/>
        @endfor
    </svg>

    <p class="mv-intro__name">{{ $page->displayName }}</p>
    <p class="mv-intro__hint">{{ $invCopy['intro_hint'] ?? 'Toca el móvil para que gire' }}</p>

    {{-- La luz del cuarto, que se enciende desde el móvil --}}
    <span class="mv-intro__light" aria-hidden="true"></span>
</div>
