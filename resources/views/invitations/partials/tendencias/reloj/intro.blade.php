{{--
    Apertura de «A la misma hora»: dos relojes frente a frente, uno por cada novio, con su nombre en la
    esfera y cada uno marcando su propia hora; los segunderos corren desparejos. Al tocarlos se les da
    cuerda: las agujas giran hasta la hora de la boda, los segunderos se sincronizan y las dos esferas
    se acercan hasta volverse una, con los dos nombres. Después se entra en la esfera hasta la portada.
    Solo se anima transform y opacity. Lógica en shell/cover-component; estilos en
    css/invitation/tendencias/reloj.css.
--}}
@php
    $introDate = \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('l j \d\e F · H:i'));
    $names = $page->names();
    // La hora de la boda en ángulos de las agujas; cada reloj arranca en otra hora y da vueltas hasta ella
    $hour = (int) $page->eventDate->format('G') % 12;
    $minute = (int) $page->eventDate->format('i');
    $targetHour = $hour * 30 + $minute * 0.5 + 360;
    $targetMinute = $minute * 6 + 720;
    $watches = [
        ['left', $names[0] ?? $page->displayName, 305, 60, '-7s'],
        ['right', $names[1] ?? '', 85, 300, '-41s'],
    ];
@endphp

<div class="inv-themed-intro rl-intro"
    x-data="invitationCover({ part: 1900, reveal: 3200, close: 4100 })"
    x-show="!closed"
    :class="{ 'is-wound': stage >= 1, 'is-open': stage >= 2 }"
    role="dialog"
    aria-modal="true"
    aria-label="Invitación a la boda de {{ $page->displayName }}">
    <p class="rl-intro__eyebrow">{{ $invCopy['intro_eyebrow'] ?? 'Desde ese día, un mismo tiempo' }}</p>

    <button type="button" class="rl-pair" data-cover-trigger @click="open()" aria-label="Dar cuerda a los dos relojes">
        @foreach($watches as [$side, $name, $startHour, $startMinute, $secondDelay])
            @include('invitations.partials.tendencias.reloj.dial', [
                'class' => 'rl-watch--'.$side,
                'name' => $name,
                'style' => "--h0: {$startHour}deg; --m0: {$startMinute}deg; --h1: {$targetHour}deg; --m1: {$targetMinute}deg; --s-delay: {$secondDelay}",
            ])
        @endforeach
    </button>

    <div class="rl-intro__together">
        <p class="rl-intro__names">{{ $page->displayName }}</p>
        <p class="rl-intro__date">{{ $introDate }}</p>
        @if($guest)
            <p class="rl-intro__guest">{{ $invCopy['guest_banner_eyebrow'] ?? 'Con tiempo reservado para' }} {{ $guest->name }}</p>
        @endif
    </div>

    <p class="rl-intro__hint">{{ $invCopy['intro_hint'] ?? 'Toca la corona para darles cuerda' }}</p>
</div>
