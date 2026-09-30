{{--
    Apertura de «Cuento desplegable»: el libro cerrado, con tapa de tela, su marco estampado y el
    título «El cuento de…». Mientras se espera, el libro respira y un brillo recorre el estampado. Al
    tocarlo se abre la tapa y, sobre la página, se levantan una tras otra las capas de papel recortado
    del desplegable: el ventanal con estrellas, la escalera del salón con sus columnas, los arbustos de
    adelante y, al final, las letras de su nombre que se ponen de pie. Después se entra en la página
    hasta la portada. Solo se anima transform y opacity. Lógica en shell/cover-component; estilos en
    css/invitation/tendencias/cuento.css.
--}}
@php
    $introDate = \Illuminate\Support\Str::ucfirst($page->eventDate->locale('es')->translatedFormat('j \d\e F · H:i'));
    $firstName = \Illuminate\Support\Str::of($page->displayName)->explode(' ')->first();
    $letters = mb_str_split($firstName);
@endphp

<div class="inv-themed-intro cu-intro"
    x-data="invitationCover({ part: 1500, reveal: 2800, close: 3700 })"
    x-show="!closed"
    :class="{ 'is-opening': stage >= 1, 'is-open': stage >= 2 }"
    role="dialog"
    aria-modal="true"
    aria-label="Invitación al cuento de {{ $page->displayName }}">
    <p class="cu-intro__eyebrow">{{ $invCopy['intro_eyebrow'] ?? 'Érase una vez' }}</p>

    <div class="cu-book">
        {{-- La página de adentro con el desplegable --}}
        <div class="cu-book__page" aria-hidden="true">
            <p class="cu-popup__name">
                @foreach($letters as $letter)
                    <span style="--l: {{ $loop->index }}">{{ $letter }}</span>
                @endforeach
            </p>
            <div class="cu-popup">
                {{-- Atrás: el ventanal del salón con sus estrellas --}}
                <svg class="cu-popup__layer cu-popup__layer--back" viewBox="0 0 200 150" preserveAspectRatio="xMidYMax meet" focusable="false">
                    <path class="cu-paper--accent" d="M34 150 V72 A66 66 0 0 1 166 72 V150 Z"/>
                    <path class="cu-paper--page" d="M52 150 V78 A48 48 0 0 1 148 78 V150 Z"/>
                    <path class="cu-paper--line" d="M100 30 V150 M52 104 H148"/>
                    @foreach([[76, 60], [124, 60], [100, 44], [70, 88], [130, 88]] as [$x, $y])
                        <path class="cu-paper--accent" d="M{{ $x }} {{ $y - 5 }} L{{ $x + 1.6 }} {{ $y - 1.6 }} L{{ $x + 5 }} {{ $y }} L{{ $x + 1.6 }} {{ $y + 1.6 }} L{{ $x }} {{ $y + 5 }} L{{ $x - 1.6 }} {{ $y + 1.6 }} L{{ $x - 5 }} {{ $y }} L{{ $x - 1.6 }} {{ $y - 1.6 }} Z"/>
                    @endforeach
                </svg>
                {{-- En medio: la escalera del vals con sus dos columnas --}}
                <svg class="cu-popup__layer cu-popup__layer--mid" viewBox="0 0 200 150" preserveAspectRatio="xMidYMax meet" focusable="false">
                    <path class="cu-paper--secondary" d="M40 150 H160 V143 H151 V136 H142 V129 H133 V122 H124 V115 H76 V122 H67 V129 H58 V136 H49 V143 H40 Z"/>
                    <path class="cu-paper--page" d="M76 115 H124 V112 H76 Z M49 143 H151 V141 H49 Z" opacity="0.45"/>
                    <path class="cu-paper--secondary" d="M14 150 V84 H10 V76 H36 V84 H32 V150 Z M168 150 V84 H164 V76 H190 V84 H186 V150 Z"/>
                    <circle class="cu-paper--secondary" cx="23" cy="70" r="6"/>
                    <circle class="cu-paper--secondary" cx="177" cy="70" r="6"/>
                </svg>
                {{-- Adelante: los arbustos recortados a los dos lados --}}
                <svg class="cu-popup__layer cu-popup__layer--front" viewBox="0 0 200 150" preserveAspectRatio="xMidYMax meet" focusable="false">
                    <path class="cu-paper--primary" d="M0 150 V132 C0 123 8 119 14 123 C16 114 28 113 32 121 C38 117 47 121 45 130 C51 132 53 141 48 150 Z"/>
                    <path class="cu-paper--primary" d="M200 150 V132 C200 123 192 119 186 123 C184 114 172 113 168 121 C162 117 153 121 155 130 C149 132 147 141 152 150 Z"/>
                    @foreach([[13, 129], [29, 123], [39, 137], [187, 129], [171, 123], [161, 137]] as [$x, $y])
                        <circle class="cu-paper--accent" cx="{{ $x }}" cy="{{ $y }}" r="3.4"/>
                    @endforeach
                </svg>
            </div>
        </div>

        {{-- La tapa: se toca para abrir el libro --}}
        <button type="button" class="cu-book__cover" data-cover-trigger @click="open()" aria-label="Abrir el libro">
            <span class="cu-cover__front">
                <span class="cu-cover__frame" aria-hidden="true"></span>
                <span class="cu-cover__kicker">{{ $invCopy['book_title'] ?? 'El cuento de' }}</span>
                <span class="cu-cover__name">{{ $page->displayName }}</span>
                <span class="cu-cover__star" aria-hidden="true"></span>
                <span class="cu-cover__xv">XV</span>
                <span class="cu-cover__shine" aria-hidden="true"></span>
            </span>
            <span class="cu-cover__back" aria-hidden="true"></span>
        </button>
    </div>

    <div class="cu-intro__meta">
        <p class="cu-intro__date">{{ $introDate }}</p>
        @if($guest)
            <p class="cu-intro__guest">{{ $invCopy['guest_banner_eyebrow'] ?? 'Este cuento es para' }} {{ $guest->name }}</p>
        @endif
    </div>

    <p class="cu-intro__hint">{{ $invCopy['intro_hint'] ?? 'Toca el libro para abrirlo' }}</p>
</div>
