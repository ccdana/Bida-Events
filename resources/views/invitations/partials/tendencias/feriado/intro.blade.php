{{--
    Apertura de «Día feriado»: el almanaque de hojas que cuelga en la pared de la casa. Arriba, el
    cartón con el año y el ojal del clavo; abajo, el bloque de hojas pegado por su lomo, con la hoja de
    uno de los días que faltan encima. Al tocarlo se arrancan las hojas una tras otra (los días que
    faltan hasta el cumpleaños) y queda la del cumpleaños: impresa en rojo como feriado, con el nombre,
    el círculo de bolígrafo alrededor del número y la nota a mano. Después se acerca el almanaque hasta
    la portada. Solo se anima transform y opacity. Lógica en shell/cover-component; estilos en
    css/invitation/tendencias/feriado.css.
--}}
@php
    $date = $page->eventDate->locale('es');
    $month = fn ($day) => \Illuminate\Support\Str::ucfirst($day->translatedFormat('F'));
    $weekday = fn ($day) => \Illuminate\Support\Str::ucfirst($day->translatedFormat('l'));
    // Las hojas que se arrancan: los cuatro días antes del cumpleaños. En el bloque va abajo la más
    // cercana y arriba la más lejana, que es la primera en arrancarse (--i).
    $pastLeaves = collect([1, 2, 3, 4])->map(fn (int $back) => [$date->copy()->subDays($back), 4 - $back]);
@endphp

<div class="inv-themed-intro fd-intro"
    x-data="invitationCover({ part: 2900, reveal: 3500, close: 4400 })"
    x-show="!closed"
    :class="{ 'is-torn': stage >= 1, 'is-open': stage >= 2 }"
    role="dialog"
    aria-modal="true"
    aria-label="Invitación al cumpleaños de {{ $page->displayName }}">
    <p class="fd-intro__eyebrow">{{ $invCopy['intro_eyebrow'] ?? 'Este año el almanaque trae un feriado más' }}</p>

    <div class="fd-wall">
        <button type="button" class="fd-pad" data-cover-trigger @click="open()" aria-label="Arrancar las hojas del almanaque hasta el día del cumpleaños">
            <span class="fd-pad__board" aria-hidden="true">
                <span class="fd-pad__eyelet"></span>
                <span class="fd-pad__year">{{ $date->format('Y') }}</span>
            </span>

            <span class="fd-pad__stack" aria-hidden="true">
                {{-- La hoja del cumpleaños, al fondo del bloque: impresa en rojo --}}
                <span class="fd-leaf fd-leaf--day">
                    <span class="fd-leaf__band">{{ $month($date) }}</span>
                    <span class="fd-leaf__number">
                        {{ $date->format('j') }}
                        <svg class="fd-circle" viewBox="0 0 200 150" preserveAspectRatio="none" focusable="false">
                            <path d="M152 24C112 4 44 10 22 54C4 92 58 136 118 128C178 120 198 72 170 38C156 22 128 14 102 18"/>
                        </svg>
                    </span>
                    <span class="fd-leaf__weekday">{{ $weekday($date) }}</span>
                    <span class="fd-leaf__holiday">
                        <b>{{ $invCopy['cal_holiday'] ?? 'Feriado' }}</b>
                        {{ $invCopy['cal_line'] ?? 'Cumpleaños de' }} {{ $page->displayName }}
                    </span>
                    <span class="fd-leaf__note">
                        {{ $invCopy['cal_note'] ?? '¡Mi cumple!' }}
                        <svg class="fd-arrow" viewBox="0 0 60 44" focusable="false"><path d="M50 4C34 4 18 14 10 34M10 34l-2-11M10 34l10-5"/></svg>
                    </span>
                </span>

                @foreach($pastLeaves as [$day, $order])
                    <span @class(['fd-leaf', 'fd-leaf--past', 'is-sunday' => $day->isSunday()]) style="--i: {{ $order }}; --dir: {{ $order % 2 ? 1 : -1 }}">
                        <span class="fd-leaf__band">{{ $month($day) }}</span>
                        <span class="fd-leaf__number">{{ $day->format('j') }}</span>
                        <span class="fd-leaf__weekday">{{ $weekday($day) }}</span>
                    </span>
                @endforeach
            </span>
            <span class="fd-pad__edges" aria-hidden="true"></span>
        </button>

        {{-- El papelito pegado con el nombre del invitado --}}
        @if($guest)
            <p class="fd-sticky">
                <span>{{ $invCopy['guest_banner_eyebrow'] ?? 'Día reservado para' }}</span>
                <strong>{{ $guest->name }}</strong>
            </p>
        @endif
    </div>

    <p class="fd-intro__hint">{{ $invCopy['intro_hint'] ?? 'Toca el almanaque para arrancar las hojas' }}</p>
</div>
