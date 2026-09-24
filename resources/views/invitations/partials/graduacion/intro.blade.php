{{--
    Apertura de la graduación: un diploma enrollado con su cinta. El invitado toca la cinta, el lazo
    se suelta, el diploma se despliega con la invitación escrita y tres birretes salen volando.
    Lógica en graduacion-birrete.blade.php (graduationIntro).
--}}
<div class="inv-grad-intro"
    x-data="graduationIntro()"
    x-show="!closed"
    :class="{ 'is-open': opened }"
    role="dialog"
    aria-modal="true"
    aria-label="Invitación a la graduación de {{ $page->displayName }}">
    <div class="inv-grad-intro__caps" aria-hidden="true">
        @foreach([[-120, -260, -28, '0s'], [0, -320, 12, '0.08s'], [120, -250, 34, '0.16s']] as [$capX, $capY, $capR, $capDelay])
            <span style="--x: {{ $capX }}px; --y: {{ $capY }}px; --r: {{ $capR }}deg; --delay: {{ $capDelay }}">
                @include('invitations.partials.graduacion.cap')
            </span>
        @endforeach
    </div>

    <div class="inv-grad-intro__content">
        <p class="inv-grad-intro__eyebrow">
            @if($guest)
                {{ $guest->name }}, {{ mb_strtolower($invCopy['intro_eyebrow'] ?? 'Tienes una invitación') }}
            @else
                {{ $invCopy['intro_eyebrow'] ?? 'Tienes una invitación' }}
            @endif
        </p>

        <button type="button" class="inv-grad-scroll" data-cover-trigger @click="open()" aria-label="Desatar la cinta y abrir la invitación">
            <span class="inv-grad-scroll__paper">
                <span class="inv-grad-scroll__line">{{ $invCopy['hero_eyebrow'] ?? 'Me gradúo' }}</span>
                <span class="inv-grad-scroll__name">{{ $page->displayName }}</span>
                <span class="inv-grad-scroll__line">{{ \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('j \d\e F \d\e Y')) }}</span>
            </span>
            <span class="inv-grad-scroll__roll inv-grad-scroll__roll--top" aria-hidden="true"></span>
            <span class="inv-grad-scroll__roll inv-grad-scroll__roll--bottom" aria-hidden="true"></span>
            <span class="inv-grad-scroll__ribbon" aria-hidden="true">
                <svg viewBox="0 0 80 48" focusable="false">
                    <path class="inv-grad-scroll__tail" d="M36 26 L24 46 L30 44 L33 48 L40 28 Z"/>
                    <path class="inv-grad-scroll__tail" d="M44 26 L56 46 L50 44 L47 48 L40 28 Z"/>
                    <path class="inv-grad-scroll__loop" d="M40 22 C28 6 6 8 10 20 C13 30 30 28 40 24 Z"/>
                    <path class="inv-grad-scroll__loop" d="M40 22 C52 6 74 8 70 20 C67 30 50 28 40 24 Z"/>
                    <rect class="inv-grad-scroll__knot" x="34" y="17" width="12" height="11" rx="3"/>
                </svg>
            </span>
            <span class="inv-grad-scroll__tap" aria-hidden="true"></span>
        </button>

        <p class="inv-grad-intro__hint">{{ $invCopy['intro_hint'] ?? 'Toca la cinta para abrir el diploma' }}</p>
    </div>

    <p class="inv-grad-intro__cheer" x-show="opened" x-cloak>{{ $invCopy['intro_cheer'] ?? '¡Lo logramos!' }}</p>
</div>
