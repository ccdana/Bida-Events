{{--
    «Carta que florece»: la flor lacrada arriba, el mensaje escrito como carta y otra flor al
    cerrar. La tarjeta se manda a una sola persona, así que manda el texto.
--}}
<style>
    .carta-flap { width: 100%; height: 18mm; }
    .carta-body { padding: 10mm 12mm; text-align: center; }
    .carta-letter { width: 118mm; margin: 6mm auto 0; font-family: {!! ($fonts['script'] ? "'InvScript', " : '')."'DejaVu Serif', serif" !!}; font-size: 13pt; line-height: 1.75; color: {{ $colors['ink'] }}; }
    .carta-seal { width: 22mm; margin: 8mm auto 0; }
</style>

<div class="sheet is-thin">
    <table class="cover-fill">
        <tr>
            <td class="fill-cell">
                <img class="carta-flap" src="{{ $motifs['main'] }}" alt="">

                <div class="carta-body">
                    <p class="kicker">{{ $hero['subtitle'] }}</p>
                    <h1 class="name">{{ $hero['name'] }}</h1>

                    @if($hero['message'])
                        <p class="carta-letter">{{ $hero['message'] }}</p>
                    @endif

                    @include('client.exports.pdf.partials.when')

                    <img class="carta-seal" src="{{ $motifs['seal'] }}" alt="">
                </div>
            </td>
        </tr>
    </table>
</div>

@include('client.exports.pdf.partials.rsvp')
