{{--
    Apertura de «Entre nubes»: el cielo tapado por capas de nubes, unas detrás de otras, y en el medio
    una nube con el nombre. Al tocarla se infla, suelta sus bocanadas y las capas se abren hacia los
    costados —las de adelante más rápido— mientras entra la luz del sol y aparece la portada.
    Lógica en shell/cover-component; estilos en themes/nubes.css.
--}}
@php
    $introEyebrow = ($page->welcome['subtitulo'] ?? null) ?: ($invCopy['hero_eyebrow'] ?? 'Mi bautizo');
    $introDate = \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('j \d\e F \d\e Y'));
    // Capas de nubes: [lado, fila, profundidad]; las de adelante tapan más y se van más lejos
    $banks = [
        ['left', 'top', 'back'], ['right', 'top', 'back'],
        ['left', 'mid', 'mid'], ['right', 'mid', 'mid'],
        ['left', 'low', 'front'], ['right', 'low', 'front'],
    ];
@endphp

<div class="inv-themed-intro nb-intro"
    x-data="invitationCover({ part: 850, reveal: 1250, close: 2500 })"
    x-show="!closed"
    :class="{ 'is-puffing': stage >= 1, 'is-open': stage >= 2 }"
    @click="open()"
    role="dialog"
    aria-modal="true"
    aria-label="Invitación al bautizo de {{ $page->displayName }}">
    <span class="nb-intro__sky" aria-hidden="true"></span>
    <span class="nb-intro__sun" aria-hidden="true"></span>
    <span class="nb-intro__rays" aria-hidden="true"></span>

    @for($i = 0; $i < 7; $i++)
        <span class="nb-intro__sparkle" aria-hidden="true"
            style="{{ sprintf('--top:%.1f%%;--left:%.1f%%;--size:%dpx;--d:%.1fs;--delay:-%.1fs', 8 + fmod($i * 23.7, 40), fmod($i * 41.9 + 9, 92), 7 + ($i * 5) % 8, 2.4 + ($i % 3) * 0.6, fmod($i * 0.8, 3)) }}"></span>
    @endfor

    @foreach($banks as $index => [$side, $row, $depth])
        <div class="nb-bank nb-bank--{{ $side }} nb-bank--{{ $row }} nb-bank--{{ $depth }}" style="--b: {{ $index }}" aria-hidden="true">
            @include('invitations.partials.nubes.cloud', ['kind' => 'bank', 'shade' => true])
        </div>
    @endforeach

    <div class="nb-intro__content">
        <p class="nb-intro__eyebrow">
            @if($guest)
                Para {{ $guest->name }}
            @else
                {{ $introEyebrow }}
            @endif
        </p>

        <button type="button" class="nb-puff" data-cover-trigger aria-label="Abrir las nubes y entrar a la invitación">
            @include('invitations.partials.nubes.cloud', ['class' => 'nb-puff__cloud', 'shade' => true])
            {{-- El nombre llena la panza de la nube (data-fit mide la caja de __label) --}}
            <span class="nb-puff__label">
                <span class="nb-puff__name" data-fit data-fit-max="46" data-fit-min="20" style="--fit-fallback: 2rem">{{ $page->displayName }}</span>
            </span>
            {{-- Bocanadas que suelta la nube al inflarse --}}
            @for($i = 0; $i < 8; $i++)
                <span class="nb-puff__bit" style="--a: {{ $i * 45 + 20 }}deg; --i: {{ $i }}" aria-hidden="true"></span>
            @endfor
        </button>

        <p class="nb-intro__date">{{ $introDate }}</p>
        <p class="nb-intro__hint">{{ $invCopy['intro_hint'] ?? 'Toca la nube para abrir el cielo' }}</p>
    </div>
</div>
