{{--
    Apertura de XV años: telones de terciopelo en el tono de la paleta, con un marco fino, la corona y el
    nombre al centro. Al tocar, el texto se desvanece, la luz del escenario crece y los telones se recogen:
    cada paño es una sola pieza cuyos pliegues se comprimen y cuyo borde se curva hacia el lazo, sin
    franjas que se noten en pantallas grandes (lógica en shell/cover-component, estilos en themes/xv.css).
--}}
@php
    $introEyebrow = ($page->welcome['subtitulo'] ?? null) ?: ($invCopy['hero_eyebrow'] ?? 'Mis XV Años');
    $introDate = $page->eventDate->locale('es')->translatedFormat('j \d\e F \d\e Y');
@endphp

<div class="inv-xv-intro"
    x-data="invitationCover({ part: 350, reveal: 800, close: 2700 })"
    x-show="!closed"
    :class="{ 'is-opening': stage >= 1, 'is-parting': stage >= 2 }"
    @click="open()"
    role="dialog"
    aria-modal="true"
    aria-label="Invitación a los XV años de {{ $page->displayName }}">
    @foreach(['left', 'right'] as $side)
        <span class="inv-xv-intro__curtain inv-xv-intro__curtain--{{ $side }}" aria-hidden="true">
            <b class="inv-xv-intro__tie"></b>
        </span>
    @endforeach
    <span class="inv-xv-intro__rod" aria-hidden="true"></span>
    <span class="inv-xv-intro__light" aria-hidden="true"></span>

    <div class="inv-xv-intro__dust" aria-hidden="true">
        @for($i = 0; $i < 10; $i++)
            <span style="{{ sprintf('--x:%.1f%%;--s:%dpx;--d:%.1fs;--delay:-%.1fs;--dx:%dpx', fmod($i * 41.3 + 8, 100), 2 + ($i * 3) % 3, 10 + ($i % 4) * 2.5, fmod($i * 2.9, 12), (($i * 23) % 40) - 20) }}"></span>
        @endfor
    </div>

    <div class="inv-xv-intro__content">
        <span class="inv-xv-intro__frame" aria-hidden="true"><i></i><i></i><i></i><i></i></span>
        @include('invitations.partials.xv.crown', ['class' => 'inv-xv-intro__crown'])
        <p class="inv-xv-intro__eyebrow">
            @if($guest)
                Para {{ $guest->name }}
            @else
                {{ $introEyebrow }}
            @endif
        </p>
        <p class="inv-xv-intro__name">{{ $page->displayName }}</p>
        <span class="inv-xv-intro__rule" aria-hidden="true"></span>
        <p class="inv-xv-intro__date">{{ $introDate }}</p>
    </div>

    <button type="button" class="inv-xv-intro__hint" data-cover-trigger>
        <span class="inv-xv-intro__hint-icon" aria-hidden="true"><i></i><i></i></span>
        {{ $invCopy['intro_hint'] ?? 'Toca para abrir los telones' }}
    </button>
</div>
