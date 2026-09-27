{{--
    Apertura de «Birrete al aire»: el cambio de borla. El birrete espera con la borla colgando a la
    derecha, como antes de recibirse. Al tocarlo, la borla sube, cruza el tablero y cae del lado
    izquierdo meciéndose (ya se graduó); salta un «¡Lo logramos!» y el birrete sale volando dando
    vueltas con los de sus compañeros, mientras aparece la portada.
    Lógica en shell/cover-component; estilos en themes/birrete.css.
--}}
@php
    $introClass = ($invCopy['hero_class_label'] ?? 'Promoción').' '.$page->eventDate->format('Y');
@endphp

<div class="inv-themed-intro br-intro"
    x-data="invitationCover({ part: 1650, reveal: 2000, close: 3100 })"
    x-show="!closed"
    :class="{ 'is-turning': stage >= 1, 'is-tossed': stage >= 2 }"
    @click="open()"
    role="dialog"
    aria-modal="true"
    aria-label="Invitación a la graduación de {{ $page->displayName }}">
    <span class="br-intro__stage" aria-hidden="true"></span>

    <div class="br-intro__content">
        <p class="br-intro__eyebrow">
            @if($guest)
                Para {{ $guest->name }}
            @else
                {{ $invCopy['intro_eyebrow'] ?? 'Tienes una invitación' }}
            @endif
        </p>
        <p class="br-intro__name">{{ $page->displayName }}</p>
        <p class="br-intro__class">{{ $introClass }}</p>

        <button type="button" class="br-intro__cap" data-cover-trigger aria-label="Pasar la borla al otro lado y abrir la invitación">
            @include('invitations.partials.birrete.cap', ['side' => 'both'])
            <span class="br-intro__shadow" aria-hidden="true"></span>
            {{-- Destellos donde cae la borla --}}
            @for($i = 0; $i < 6; $i++)
                <span class="br-intro__spark" style="--a: {{ $i * 60 + 15 }}deg; --i: {{ $i }}" aria-hidden="true"></span>
            @endfor
        </button>

        <p class="br-intro__cheer" aria-hidden="true">{{ $invCopy['intro_cheer'] ?? '¡Lo logramos!' }}</p>
        <p class="br-intro__hint">{{ $invCopy['intro_hint'] ?? 'Toca la borla para pasarla al otro lado' }}</p>
    </div>

    {{-- Los birretes de los compañeros que salen volando con el suyo --}}
    @foreach([[12, -18, 0.55, 0.05], [30, 22, 0.7, 0.18], [58, -30, 0.5, 0.1], [76, 26, 0.65, 0.24], [90, -14, 0.45, 0.14]] as $index => [$left, $spin, $size, $delay])
        <span class="br-intro__flyer" style="--l: {{ $left }}%; --spin: {{ $spin }}; --s: {{ $size }}; --d: {{ $delay }}s" aria-hidden="true">
            @include('invitations.partials.birrete.cap', ['side' => $index % 2 ? 'left' : 'right'])
        </span>
    @endforeach
</div>
