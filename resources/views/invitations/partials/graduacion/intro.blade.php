{{--
    Apertura de la graduación: un diploma enrollado con su cinta, bajo un haz de luz dorada. El
    invitado toca la cinta: el nudo se suelta y los lazos se abren, el diploma se despliega con la
    invitación escrita, cae el sello dorado, estalla el confeti y los birretes salen volando.
    Lógica en graduacion-birrete.blade.php (graduationIntro); estilos en themes/graduacion.css.
--}}
<div class="inv-grad-intro"
    x-data="graduationIntro()"
    x-show="!closed"
    :class="{ 'is-open': opened }"
    role="dialog"
    aria-modal="true"
    aria-label="Invitación a la graduación de {{ $page->displayName }}">
    {{-- Haz de luz que gira despacio detrás del diploma y polvo dorado suspendido --}}
    <div class="inv-grad-intro__rays" aria-hidden="true"></div>
    <div class="inv-grad-intro__dust" aria-hidden="true">
        @for($dust = 0; $dust < 16; $dust++)
            <i style="{{ sprintf('--x:%.1f%%;--y:%.1f%%;--s:%dpx;--d:%.1fs;--delay:-%.1fs', fmod($dust * 37.3 + 5, 96), fmod($dust * 23.9 + 11, 90), 2 + ($dust % 3), 6 + ($dust % 5), fmod($dust * 1.7, 8)) }}"></i>
        @endfor
    </div>

    <div class="inv-grad-intro__caps" aria-hidden="true">
        @foreach([[-150, -250, -32, '0s'], [-70, -330, -14, '0.06s'], [0, -380, 8, '0.12s'], [75, -320, 22, '0.04s'], [150, -240, 36, '0.1s'], [-110, -180, -40, '0.18s'], [115, -190, 44, '0.16s']] as [$capX, $capY, $capR, $capDelay])
            <span style="--x: {{ $capX }}px; --y: {{ $capY }}px; --r: {{ $capR }}deg; --delay: {{ $capDelay }}">
                @include('invitations.partials.graduacion.cap')
            </span>
        @endforeach
    </div>

    {{-- Confeti de papel dorado y azul de toga: sale del centro al desplegarse el diploma --}}
    <div class="inv-grad-intro__confetti" aria-hidden="true">
        @for($piece = 0; $piece < 26; $piece++)
            @php
                $angle = deg2rad(-90 + (($piece * 137.5) % 180) - 90);
                $reach = 120 + ($piece * 29) % 130;
            @endphp
            <i class="{{ $piece % 3 === 0 ? 'is-robe' : '' }}" style="{{ sprintf('--dx:%dpx;--dy:%dpx;--r:%ddeg;--delay:%dms;--w:%dpx', (int) round(cos($angle) * $reach), (int) round(sin($angle) * $reach) - 40, 180 + ($piece * 67) % 540, ($piece * 23) % 220, 5 + $piece % 4) }}"></i>
        @endfor
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
                {{-- Sello dorado que cae sobre el diploma ya abierto --}}
                <span class="inv-grad-scroll__seal" aria-hidden="true">
                    <svg viewBox="0 0 40 40" focusable="false">
                        <path class="inv-grad-scroll__seal-edge" d="M20 1.5l3.1 2.6 4-.7 1.8 3.6 3.9 1.2.2 4 3 2.7-1.4 3.8 1.4 3.8-3 2.7-.2 4-3.9 1.2-1.8 3.6-4-.7L20 38.5l-3.1-2.6-4 .7-1.8-3.6-3.9-1.2-.2-4-3-2.7 1.4-3.8-1.4-3.8 3-2.7.2-4 3.9-1.2 1.8-3.6 4 .7z"/>
                        <circle class="inv-grad-scroll__seal-ring" cx="20" cy="20" r="11.5"/>
                        <path class="inv-grad-scroll__seal-star" d="M20 13.2l1.9 4.1 4.4.5-3.3 3 .9 4.4-3.9-2.2-3.9 2.2.9-4.4-3.3-3 4.4-.5z"/>
                    </svg>
                </span>
            </span>
            <span class="inv-grad-scroll__roll inv-grad-scroll__roll--top" aria-hidden="true"></span>
            <span class="inv-grad-scroll__roll inv-grad-scroll__roll--bottom" aria-hidden="true"></span>
            <span class="inv-grad-scroll__ribbon" aria-hidden="true">
                <svg viewBox="0 0 80 48" focusable="false">
                    <path class="inv-grad-scroll__tail inv-grad-scroll__tail--left" d="M36 26 L24 46 L30 44 L33 48 L40 28 Z"/>
                    <path class="inv-grad-scroll__tail inv-grad-scroll__tail--right" d="M44 26 L56 46 L50 44 L47 48 L40 28 Z"/>
                    <path class="inv-grad-scroll__loop inv-grad-scroll__loop--left" d="M40 22 C28 6 6 8 10 20 C13 30 30 28 40 24 Z"/>
                    <path class="inv-grad-scroll__loop inv-grad-scroll__loop--right" d="M40 22 C52 6 74 8 70 20 C67 30 50 28 40 24 Z"/>
                    <rect class="inv-grad-scroll__knot" x="34" y="17" width="12" height="11" rx="3"/>
                </svg>
            </span>
            <span class="inv-grad-scroll__tap" aria-hidden="true"></span>
        </button>

        <p class="inv-grad-intro__hint">{{ $invCopy['intro_hint'] ?? 'Toca la cinta para abrir el diploma' }}</p>
    </div>

    <p class="inv-grad-intro__cheer" x-show="opened" x-cloak>{{ $invCopy['intro_cheer'] ?? '¡Lo logramos!' }}</p>
</div>
