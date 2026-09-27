{{--
    Una nube de «Entre nubes», dibujada con la paleta (el color lo pone la hoja: fill currentColor).
    kind: puff (nube suelta, 200×120) o bank (banco de nubes largo, 400×150, con las puntas redondeadas).
    Con shade, la panza lleva un tono más oscuro que le da volumen. class: clases extra.
--}}
@if(($kind ?? 'puff') === 'bank')
    <svg class="nb-cloud nb-cloud--bank {{ $class ?? '' }}" viewBox="0 0 400 150" preserveAspectRatio="none" aria-hidden="true" focusable="false">
        <path d="M26 142 C10 142 0 130 0 114 C0 98 12 86 28 86 C30 64 50 50 72 54 C82 30 108 18 134 26 C148 8 180 4 200 20 C214 6 246 6 260 26 C278 14 308 18 318 42 C338 34 364 44 370 66 C388 70 400 86 400 104 C400 124 386 138 366 140 C300 148 90 148 26 142 Z"/>
        @if(!empty($shade))
            <path class="nb-cloud__shade" d="M26 142 C14 142 5 136 2 126 C90 138 310 138 399 112 C396 128 384 138 366 140 C300 148 90 148 26 142 Z"/>
        @endif
    </svg>
@else
    <svg class="nb-cloud {{ $class ?? '' }}" viewBox="0 0 200 120" aria-hidden="true" focusable="false">
        <path d="M38 112 C17 112 4 99 4 81 C4 63 18 51 36 50 C37 30 53 15 74 15 C89 15 101 22 108 34 C114 28 123 24 133 24 C153 24 169 39 171 58 C187 60 197 72 197 86 C197 101 185 112 169 112 Z"/>
        @if(!empty($shade))
            <path class="nb-cloud__shade" d="M38 112 C22 112 10 104 7 92 C40 100 120 104 194 92 C191 104 181 112 169 112 Z"/>
        @endif
    </svg>
@endif
