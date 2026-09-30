{{--
    «Joyero musical»: la tarjeta de la joyería. Un marco doble como el filete de oro del joyero, la
    gema arriba, su nombre, el mensaje y la fecha, la hora y el lugar.
--}}
<style>
    .jo-card { width: 132mm; margin: 4mm auto 0; padding: 3mm; border: 1.4pt solid {{ $colors['accent'] }}; }
    .jo-card-inner { padding: 9mm 8mm; border: 0.6pt solid {{ $colors['line'] }}; background: {{ $colors['tint'] }}; }
    .jo-card .gem { width: 18mm; margin: 0 auto 4mm; }
    .jo-card .kicker { margin: 0; }
    .jo-card .name { margin: 3mm 0 0; }
</style>

<div class="sheet is-double">
    <table class="cover-fill">
        <tr>
            <td class="fill-cell center">
                <div class="jo-card">
                    <div class="jo-card-inner">
                        <img class="gem" src="{{ $motifs['main'] }}" alt="">
                        <p class="kicker">{{ $copy['jewel_card'] ?? $hero['subtitle'] }}</p>
                        <h1 class="name">{{ $hero['name'] }}</h1>

                        @if($hero['message'])
                            <p class="message" style="margin-top: 6mm;">{{ $hero['message'] }}</p>
                        @endif
                    </div>
                </div>

                @include('client.exports.pdf.partials.when')
            </td>
        </tr>
    </table>
</div>

@include('client.exports.pdf.partials.rsvp')
