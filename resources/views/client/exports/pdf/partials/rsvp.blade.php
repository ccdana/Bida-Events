{{--
    El puente entre el papel y la invitación digital: el QR que la abre y, si la invitación
    pide confirmación, el aviso de que ahí mismo se confirma. Recibe $rsvp y $hashtag.
--}}
<table class="strip">
    <tr>
        @if($rsvp['qr'])
            <td class="qr-cell"><img class="qr" src="{{ $rsvp['qr'] }}" alt=""></td>
        @endif
        <td>
            <p class="strip-title">{{ $rsvp['title'] }}</p>
            @if($rsvp['message'])
                <p>{{ $rsvp['message'] }}</p>
            @endif
            <p class="muted">Escanea el código para ver la invitación completa{{ $rsvp['enabled'] ? ' y confirmar tu asistencia' : '' }}.</p>
            <p class="url">{{ $rsvp['url'] }}</p>
            @if($hashtag)
                <p class="muted" style="margin-top: 2mm;">Comparte tus fotos con <span class="strong">{{ $hashtag }}</span></p>
            @endif
        </td>
    </tr>
</table>
