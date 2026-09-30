{{--
    «Caleidoscopio»: sobre la noche, un «XV» enorme con los tres colores del prisma en franjas, los
    destellos, su nombre y las facetas con la fecha, la hora y el lugar.
--}}
<style>
    .ka-xv { margin: 4mm 0 0; font-size: 92pt; font-weight: bold; line-height: 1; letter-spacing: -4pt; color: {{ $colors['primary'] }}; }
    .ka-prism { width: 70mm; margin: 2mm auto 0; border-collapse: collapse; }
    .ka-prism td { height: 1.4mm; padding: 0; }
    .ka-sparks { width: 46mm; margin: 5mm auto 0; }
</style>

<div class="sheet is-none">
    <table class="cover-fill">
        <tr>
            <td class="fill-cell center">
                <p class="kicker">{{ $hero['subtitle'] }}</p>
                <p class="ka-xv">XV</p>
                <table class="ka-prism">
                    <tr>
                        <td style="background: {{ $colors['primary'] }};"></td>
                        <td style="background: {{ $colors['accent'] }};"></td>
                        <td style="background: {{ $colors['line'] }};"></td>
                    </tr>
                </table>

                <h1 class="name" style="margin-top: 7mm;">{{ $hero['name'] }}</h1>
                <img class="ka-sparks" src="{{ $motifs['main'] }}" alt="">

                @if($hero['message'])
                    <p class="message" style="margin-top: 6mm;">{{ $hero['message'] }}</p>
                @endif

                @include('client.exports.pdf.partials.when')
            </td>
        </tr>
    </table>
</div>

@include('client.exports.pdf.partials.rsvp')
