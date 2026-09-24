{{--
    Apertura de Halloween: una calabaza apagada bajo la luna. El invitado la toca, la vela se enciende
    dentro (la cara brilla y titila), salen murciélagos volando y la noche se abre.
    Lógica en halloween-calabazas.blade.php (halloweenIntro).
--}}
<div class="inv-hw-intro"
    x-data="halloweenIntro()"
    x-show="!closed"
    :class="{ 'is-lit': lit }"
    role="dialog"
    aria-modal="true"
    aria-label="Invitación a {{ $page->displayName }}">
    <span class="inv-hw-intro__moon" aria-hidden="true"></span>
    @include('invitations.partials.halloween.landscape', ['class' => 'inv-hw-intro__land'])

    <div class="inv-hw-intro__bats" aria-hidden="true">
        @foreach([[-150, -230, 0.9, '0s'], [-40, -300, 1.1, '0.1s'], [70, -260, 0.8, '0.05s'], [160, -200, 1, '0.18s'], [10, -180, 0.7, '0.24s']] as [$batX, $batY, $batScale, $batDelay])
            <span style="--x: {{ $batX }}px; --y: {{ $batY }}px; --s: {{ $batScale }}; --delay: {{ $batDelay }}">
                @include('invitations.partials.halloween.bat')
            </span>
        @endforeach
    </div>

    <div class="inv-hw-intro__content">
        <p class="inv-hw-intro__eyebrow">
            @if($guest)
                {{ $guest->name }}, {{ mb_strtolower($invCopy['intro_eyebrow'] ?? 'Te espera una noche de miedo') }}
            @else
                {{ $invCopy['intro_eyebrow'] ?? 'Te espera una noche de miedo' }}
            @endif
        </p>
        <p class="inv-hw-intro__name">{{ $page->displayName }}</p>

        <button type="button" class="inv-hw-lantern" data-cover-trigger @click="light()" aria-label="Encender la calabaza y abrir la invitación">
            <span class="inv-hw-lantern__glow" aria-hidden="true"></span>
            @include('invitations.partials.halloween.pumpkin', ['class' => 'inv-hw-lantern__pumpkin'])
            <span class="inv-hw-lantern__tap" aria-hidden="true"></span>
        </button>

        <p class="inv-hw-intro__hint">{{ $invCopy['intro_hint'] ?? 'Toca la calabaza para encenderla' }}</p>
    </div>

    <p class="inv-hw-intro__cheer" x-show="lit" x-cloak>{{ $invCopy['intro_cheer'] ?? '¡Que empiece la fiesta!' }}</p>
</div>
