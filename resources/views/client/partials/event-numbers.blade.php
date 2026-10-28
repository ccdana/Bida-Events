{{-- Los números de un evento, sueltos y en una línea: confirmaron, sin responder y no asisten (o, en una tarjeta, las respuestas y los aportes). --}}
@if($row['isCard'])
    <p class="ev-nums">
        <span><x-phosphor-chat-circle-text-light aria-hidden="true" /> <b>{{ $row['replies'] }}</b> <span class="sr-only">respuestas</span></span>
        <span><x-phosphor-image-light aria-hidden="true" /> <b>{{ $row['others'] }}</b> <span class="sr-only">fotos y canciones</span></span>
    </p>
@elseif($row['total'] > 0)
    <p class="ev-nums">
        <span title="Confirmaron"><x-phosphor-check-circle-light aria-hidden="true" /> <b>{{ $row['confirmed'] }}</b> <span class="sr-only">confirmaron</span></span>
        <span title="Sin responder"><x-phosphor-clock-light aria-hidden="true" /> <b>{{ $row['pending'] }}</b> <span class="sr-only">sin responder</span></span>
        <span title="No asisten"><x-phosphor-x-circle-light aria-hidden="true" /> <b>{{ $row['declined'] }}</b> <span class="sr-only">no asisten</span></span>
    </p>
@endif
