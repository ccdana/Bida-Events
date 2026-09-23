{{--
    Portada neutra: la que usa cualquier plantilla que no declare una propia. El adorno de la
    plantilla arriba y abajo, y el nombre al centro de la hoja.
--}}
<div class="sheet is-{{ $style['frame'] }}">
    <table class="cover-fill">
        <tr>
            <td class="fill-cell center">
                <img class="motif" src="{{ $motifs['main'] }}" alt="">
                <p class="kicker">{{ $hero['subtitle'] }}</p>
                <h1 class="name">{{ $hero['name'] }}</h1>

                @if($hero['message'])
                    <p class="message" style="margin-top: 6mm;">{{ $hero['message'] }}</p>
                @endif

                @include('client.exports.pdf.partials.when')

                <img class="motif" src="{{ $motifs['main'] }}" alt="" style="margin-top: 7mm;">
            </td>
        </tr>
    </table>
</div>

@include('client.exports.pdf.partials.rsvp')
